<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Document.php';
require_once __DIR__ . '/helpers/file_helper.php';

Auth::requireLogin();
$user = Auth::user();
$docModel = new Document();
$db = Database::getInstance();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$doc = $docModel->getById($id);

if (!$doc) {
    header("Location: documents.php");
    exit;
}

$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name ASC");
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $docNumber   = trim($_POST['doc_number'] ?? '');
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $docType     = trim($_POST['doc_type'] ?? 'luu');
    $summary     = trim($_POST['summary'] ?? '');
    $issuedDate  = trim($_POST['issued_date'] ?? '');
    $dueDate     = trim($_POST['due_date'] ?? '');

    if (empty($title) || empty($docNumber)) {
        $error = 'Vui lòng nhập Tên văn bản và Số hiệu!';
    } else {
        $filePath = null;
        $fileType = null;
        $fileSize = 0;
        $extractedText = '';

        if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = FileHelper::uploadFile($_FILES['file']);
            if ($uploadResult['success']) {
                $filePath = $uploadResult['path'];
                $fileType = $uploadResult['file_type'];
                $fileSize = $uploadResult['file_size'];
                $extractedText = $uploadResult['extracted_text'];
            } else {
                $error = $uploadResult['message'];
            }
        }

        if (!$error) {
            $data = [
                'category_id'    => $categoryId,
                'title'          => $title,
                'doc_number'     => $docNumber,
                'summary'        => $summary,
                'extracted_text' => $extractedText,
                'doc_type'       => $docType,
                'file_path'      => $filePath,
                'file_type'      => $fileType,
                'file_size'      => $fileSize,
                'issued_date'    => $issuedDate,
                'due_date'       => $dueDate
            ];

            $docModel->update($id, $data);
            header("Location: document_detail.php?id={$id}");
            exit;
        }
    }
}

$pageTitle = 'Chỉnh Sửa Văn Bản';
require_once __DIR__ . '/views/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Chỉnh Sửa Văn Bản</h3>
                <p class="text-muted mb-0">Cập nhật thông tin chi tiết hoặc đính kèm lại tệp mới</p>
            </div>
            <a href="document_detail.php?id=<?= $doc['id'] ?>" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Hủy & Quay lại
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="document_edit.php?id=<?= $doc['id'] ?>" enctype="multipart/form-data">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="bi bi-pencil-square me-2"></i>Thông Tin Văn Bản</h5>
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold">Tên / Tiêu đề văn bản <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($doc['title']) ?>" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Số hiệu văn bản <span class="text-danger">*</span></label>
                            <input type="text" name="doc_number" class="form-control" value="<?= htmlspecialchars($doc['doc_number']) ?>" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Phân loại văn bản</label>
                            <select name="doc_type" class="form-select">
                                <option value="luu" <?= $doc['doc_type'] === 'luu' ? 'selected' : '' ?>>Văn bản lưu</option>
                                <option value="thuc_hien" <?= $doc['doc_type'] === 'thuc_hien' ? 'selected' : '' ?>>Văn bản thực hiện</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Danh mục</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" <?= $doc['category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Ngày ban hành</label>
                            <input type="date" name="issued_date" class="form-control" value="<?= $doc['issued_date'] ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Hạn xử lý</label>
                            <input type="date" name="due_date" class="form-control" value="<?= $doc['due_date'] ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="bi bi-paperclip me-2"></i>Tệp Đính Kèm & Trích Yếu</h5>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Thay thế file đính kèm (Để trống nếu giữ nguyên file cũ)</label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx">
                            <?php if ($doc['file_path']): ?>
                                <small class="text-success d-block mt-1"><i class="bi bi-check-circle me-1"></i>File hiện tại: <?= htmlspecialchars($doc['file_path']) ?></small>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Trích yếu nội dung văn bản</label>
                            <textarea name="summary" class="form-control" rows="4"><?= htmlspecialchars($doc['summary']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="document_detail.php?id=<?= $doc['id'] ?>" class="btn btn-secondary px-4">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <i class="bi bi-save me-1"></i> Cập Nhật
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/views/footer.php'; ?>
