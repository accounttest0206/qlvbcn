<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Document.php';

Auth::requireLogin();
$user = Auth::user();
$docModel = new Document();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$doc = $docModel->getById($id);

if (!$doc) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Văn bản không tồn tại hoặc đã bị xóa. <a href='documents.php'>Quay lại</a></div></div>";
    exit;
}

$message = '';
$messageType = 'success';

// Xử lý Cập nhật Tiến độ / Đánh dấu hoàn thành
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_progress') {
    $newProgress = (int)($_POST['progress'] ?? 0);
    $newStatus   = trim($_POST['status'] ?? 'chua_xu_ly');
    $note        = trim($_POST['note'] ?? '');

    if (isset($_POST['mark_complete'])) {
        $newProgress = 100;
        $newStatus = 'hoan_thanh';
        $note = $note ?: 'Đánh dấu hoàn thành công việc.';
    }

    if ($docModel->updateProgress($id, $user['id'], $newProgress, $newStatus, $note)) {
        $message = 'Cập nhật tiến độ thành công!';
        $doc = $docModel->getById($id); // refresh
    } else {
        $message = 'Cập nhật thất bại!';
        $messageType = 'danger';
    }
}

$logs = $docModel->getProgressLogs($id);
$pageTitle = 'Chi Tiết Văn Bản: ' . $doc['title'];

require_once __DIR__ . '/views/header.php';
?>

<!-- Breadcrumb Navigation -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="documents.php">Quản lý văn bản</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($doc['doc_number']) ?></li>
    </ol>
</nav>

<!-- Alert Message -->
<?php if ($message): ?>
    <div class="alert alert-<?= $messageType ?> alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Header Info Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-primary fs-6"><i class="bi bi-hash"></i> <?= htmlspecialchars($doc['doc_number']) ?></span>
                    <?php if ($doc['doc_type'] === 'luu'): ?>
                        <span class="badge bg-info text-dark">Văn bản lưu</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">Văn bản thực hiện</span>
                    <?php endif; ?>
                    <span class="badge bg-body-tertiary text-body border"><?= htmlspecialchars($doc['category_name'] ?? 'Chưa phân loại') ?></span>
                </div>
                <h3 class="fw-bold mb-2"><?= htmlspecialchars($doc['title']) ?></h3>
                <div class="text-muted small d-flex flex-wrap gap-3">
                    <span><i class="bi bi-person me-1"></i> Người tạo: <strong><?= htmlspecialchars($doc['author_name']) ?></strong></span>
                    <span><i class="bi bi-calendar3 me-1"></i> Ngày ban hành: <strong><?= $doc['issued_date'] ? date('d/m/Y', strtotime($doc['issued_date'])) : 'N/A' ?></strong></span>
                    <?php if ($doc['due_date']): ?>
                        <span class="text-danger"><i class="bi bi-clock-history me-1"></i> Hạn xử lý: <strong><?= date('d/m/Y', strtotime($doc['due_date'])) ?></strong></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="document_edit.php?id=<?= $doc['id'] ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-pencil me-1"></i> Chỉnh sửa
                </a>
                <?php if ($doc['file_path']): ?>
                    <a href="<?= htmlspecialchars($doc['file_path']) ?>" download class="btn btn-outline-primary">
                        <i class="bi bi-download me-1"></i> Tải về (.<?= $doc['file_type'] ?>)
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <hr class="my-3">

        <!-- Status & Progress Bar -->
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="fw-semibold">Trạng thái:</span>
                    <?php if ($doc['status'] === 'hoan_thanh'): ?>
                        <span class="badge bg-success fs-6"><i class="bi bi-check-circle-fill me-1"></i> Hoàn thành</span>
                    <?php elseif ($doc['status'] === 'dang_thuc_hien'): ?>
                        <span class="badge bg-info text-dark fs-6"><i class="bi bi-hourglass-split me-1"></i> Đang thực hiện</span>
                    <?php else: ?>
                        <span class="badge bg-secondary fs-6"><i class="bi bi-dash-circle me-1"></i> Chưa xử lý</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-12 col-md-8">
                <div class="d-flex align-items-center gap-3">
                    <span class="fw-semibold text-nowrap">Tiến độ: <?= $doc['progress'] ?>%</span>
                    <div class="progress flex-grow-1" style="height: 12px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated <?= $doc['progress'] == 100 ? 'bg-success' : 'bg-info' ?>" style="width: <?= $doc['progress'] ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Summaries & Inline Document Previewer -->
    <div class="col-12 col-lg-8">
        <!-- Automatic Extractive Summary Card (TF-IDF) -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary bg-opacity-10 border-0 fw-bold d-flex align-items-center justify-content-between py-3">
                <span class="text-primary"><i class="bi bi-robot me-2 fs-5"></i>Tóm Tắt Tự Động (Thuật Toán Extractive TF-IDF)</span>
                <span class="badge bg-primary">Không dùng AI</span>
            </div>
            <div class="card-body">
                <p class="card-text text-body lh-base mb-0">
                    <?= nl2br(htmlspecialchars($doc['auto_summary'] ?: 'Chưa có tóm tắt tự động.')) ?>
                </p>
            </div>
        </div>

        <!-- Trích Yếu / Nội Dung Gốc -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 fw-bold pt-3">
                <i class="bi bi-text-paragraph text-secondary me-2"></i>Trích Yếu Văn Bản
            </div>
            <div class="card-body">
                <p class="text-body lh-base mb-0">
                    <?= nl2br(htmlspecialchars($doc['summary'] ?: 'Chưa nhập trích yếu.')) ?>
                </p>
            </div>
        </div>

        <!-- Inline Document Viewer (PDF & Mammoth.js DOCX) -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 fw-bold d-flex justify-content-between align-items-center pt-3">
                <span><i class="bi bi-file-earmark-word-fill text-primary me-2"></i>Xem File Trực Tiếp Trên Trình Duyệt</span>
                <?php if ($doc['file_type']): ?>
                    <span class="badge bg-secondary">.<?= strtoupper($doc['file_type']) ?></span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <?php if (!empty($doc['file_path'])): ?>
                    <div id="documentViewerContainer"></div>
                    <script>
                        document.addEventListener('DOMContentLoaded', () => {
                            previewDocument('<?= htmlspecialchars($doc['file_path']) ?>', '<?= htmlspecialchars($doc['file_type']) ?>', 'documentViewerContainer');
                        });
                    </script>
                <?php else: ?>
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-file-earmark-x fs-1 d-block mb-2"></i>
                        Văn bản này không đính kèm file tài liệu.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Right Column: Update Progress Form & Audit Log -->
    <div class="col-12 col-lg-4">
        <!-- Update Progress Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-transparent border-0 fw-bold pt-3">
                <i class="bi bi-sliders text-primary me-2"></i>Cập Nhật Tiến Độ Công Việc
            </div>
            <div class="card-body">
                <form method="POST" action="document_detail.php?id=<?= $doc['id'] ?>">
                    <input type="hidden" name="action" value="update_progress">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Phần trăm tiến độ (%)</label>
                        <input type="range" class="form-range" name="progress" min="0" max="100" step="5" value="<?= $doc['progress'] ?>" oninput="document.getElementById('progressVal').innerText = this.value + '%'">
                        <div class="text-end fw-bold text-primary" id="progressVal"><?= $doc['progress'] ?>%</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Trạng thái xử lý</label>
                        <select name="status" class="form-select">
                            <option value="chua_xu_ly" <?= $doc['status'] === 'chua_xu_ly' ? 'selected' : '' ?>>Chưa xử lý</option>
                            <option value="dang_thuc_hien" <?= $doc['status'] === 'dang_thuc_hien' ? 'selected' : '' ?>>Đang thực hiện</option>
                            <option value="hoan_thanh" <?= $doc['status'] === 'hoan_thanh' ? 'selected' : '' ?>>Hoàn thành</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Ghi chú tiến độ</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Nhập nội dung cập nhật tiến độ..."></textarea>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary fw-semibold">
                            <i class="bi bi-save me-1"></i> Cập Nhật Tiến Độ
                        </button>
                        <button type="submit" name="mark_complete" value="1" class="btn btn-success fw-semibold">
                            <i class="bi bi-check-all me-1"></i> Đánh Dấu Hoàn Thành
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Progress History Logs -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-0 fw-bold pt-3">
                <i class="bi bi-journal-text text-secondary me-2"></i>Nhật Ký Tiến Độ
            </div>
            <div class="card-body p-0">
                <?php if (empty($logs)): ?>
                    <div class="p-3 text-center text-muted small">Chưa có nhật ký cập nhật tiến độ.</div>
                <?php else: ?>
                    <div class="list-group list-group-flush" style="max-height: 350px; overflow-y: auto;">
                        <?php foreach ($logs as $log): ?>
                            <div class="list-group-item border-0 border-bottom p-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="small"><?= htmlspecialchars($log['user_name']) ?></strong>
                                    <span class="badge bg-body-tertiary text-muted" style="font-size: 10px;"><?= date('d/m/Y H:i', strtotime($log['created_at'])) ?></span>
                                </div>
                                <div class="small text-muted mb-1">
                                    Thay đổi: <strong><?= $log['old_progress'] ?>%</strong> &rarr; <strong class="text-primary"><?= $log['new_progress'] ?>%</strong>
                                </div>
                                <?php if ($log['note']): ?>
                                    <div class="small text-body bg-body-tertiary p-2 rounded mt-1">
                                        <?= htmlspecialchars($log['note']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/views/footer.php'; ?>
