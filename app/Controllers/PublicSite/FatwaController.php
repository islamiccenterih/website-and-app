<?php

declare(strict_types=1);

namespace App\Controllers\PublicSite;

use App\Core\Controller;
use App\Core\Uploader;
use App\Models\Fatwa;
use App\Models\FatwaQuestion;

final class FatwaController extends Controller
{
    public function index(): void
    {
        $today = Fatwa::today();
        $this->view('public/fatawa', [
            'pageTitle' => page_copy('fatawa', 'title', 'Fatawa') . ' — ' . site_name(),
            'metaDescription' => page_copy('fatawa', 'lead', 'Daily fatawa from the Islamic Center. Read today’s ruling and ask a question.'),
            'today' => $today,
            'fatawa' => Fatwa::archive($today ? (int) $today['id'] : null),
        ]);
    }

    public function show(string $slug): void
    {
        $fatwa = Fatwa::bySlug($slug);
        if (!$fatwa) {
            http_response_code(404);
            (new ErrorController())->notFound();
            return;
        }
        $this->renderDetail($fatwa);
    }

    public function ask(string $slug): void
    {
        $fatwa = Fatwa::bySlug($slug);
        if (!$fatwa) {
            http_response_code(404);
            (new ErrorController())->notFound();
            return;
        }

        $this->requireCsrf();
        $back = '/fatawa/' . $fatwa['slug'] . '#ask';

        if (!empty($_POST['website'])) {
            flash('success', 'Thank you. Your question has been received.');
            redirect($back);
        }

        $result = $this->validate([
            'name' => 'required|max:120',
            'email' => 'email|max:190',
            'body' => 'max:4000',
        ], $_POST);

        $body = trim((string) ($result['data']['body'] ?? ''));
        $file = $_FILES['attachment'] ?? [];
        $hasFile = is_array($file) && (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE);

        if (!$result['ok']) {
            $_SESSION['_old'] = $result['data'];
            $_SESSION['_errors'] = $result['errors'];
            flash('error', 'Please correct the highlighted fields.');
            redirect($back);
        }

        if ($body === '' && !$hasFile) {
            $_SESSION['_old'] = $result['data'];
            $_SESSION['_errors'] = ['body' => 'Write your question or attach a file.'];
            flash('error', 'Write a question in text, or upload a file — or both.');
            redirect($back);
        }

        if (FatwaQuestion::recentCountForIp(request_ip()) >= 3) {
            flash('error', 'Please wait a few minutes before sending another question.');
            redirect($back);
        }

        $attachment = null;
        if ($hasFile) {
            try {
                $attachment = Uploader::storeAttachment($file, 'fatawa');
            } catch (\RuntimeException $e) {
                $_SESSION['_old'] = $result['data'];
                flash('error', $e->getMessage());
                redirect($back);
            }
        }

        FatwaQuestion::create([
            'fatwa_id' => (int) $fatwa['id'],
            'name' => $result['data']['name'],
            'email' => ($result['data']['email'] ?? '') !== '' ? $result['data']['email'] : null,
            'body' => $body !== '' ? $body : null,
            'attachment_path' => $attachment['path'] ?? null,
            'attachment_name' => $attachment['name'] ?? null,
            'attachment_mime' => $attachment['mime'] ?? null,
            'ip_address' => request_ip(),
            'status' => 'new',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        unset($_SESSION['_old'], $_SESSION['_errors']);
        flash('success', 'Your question is with the administration. The answer will appear under this fatwa.');
        redirect('/fatawa/' . $fatwa['slug'] . '#questions');
    }

    /**
     * @param array<string, mixed> $fatwa
     */
    private function renderDetail(array $fatwa): void
    {
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_errors']);
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);

        $more = [];
        foreach (Fatwa::published() as $row) {
            if ((int) $row['id'] === (int) $fatwa['id']) {
                continue;
            }
            $more[] = $row;
            if (count($more) >= 4) {
                break;
            }
        }

        $this->view('public/fatwa-detail', [
            'pageTitle' => Fatwa::cardTitle($fatwa) . ' — ' . site_name(),
            'metaDescription' => mb_substr(preg_replace('/\s+/', ' ', Fatwa::cardTitle($fatwa) . ' ' . (string) ($fatwa['body_en'] ?? $fatwa['body_ar'] ?? $fatwa['body_hi'] ?? '')) ?? '', 0, 160),
            'fatwa' => $fatwa,
            'blocks' => Fatwa::languageBlocks($fatwa),
            'questions' => FatwaQuestion::forFatwa((int) $fatwa['id'], true),
            'more' => $more,
            'errors' => $errors,
            'old' => $old,
        ]);
    }
}
