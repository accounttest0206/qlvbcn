<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/classes/Auth.php';
require_once __DIR__ . '/classes/Document.php';
require_once __DIR__ . '/helpers/file_helper.php';

Auth::requireLogin();
$user = Auth::user();
$docModel = new Document();
$db = Database::getInstance();

$categories = $db->fetchAll("SELECT * FROM categories ORDER BY name ASC");
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $docNumber   = trim($_POST['doc_number'] ?? '');
    $categoryId  = (int)($_POST['category_id'] ?? 0);
    $docType     = trim($_POST['doc_type'] ?? 'luu');
    $summary     = trim($_POST['summary'] ?? '');
    $issuedDate  = trim($_POST['issued_date'] ?? '');
    $dueDate     = trim($_POST['due_date'] ?? '');
    $status      = trim($_POST['status'] ?? 'chua_xu_ly');
    $progress    = (int)($_POST['progress'] ?? 0);

    if (empty($title) || empty($docNumber)) {
        $error = 'Vui lòng nhập Tên văn bản và Số hiệu văn bản!';
    } else {
        $filePath = null;
        $fileType = null;
        $fileSize = 0;
        $extractedText = '';

        // Tải file lên nếu có
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
                'user_id'        => $user['id'],
                'category_id'    => $categoryId,
                'title'          => $title,
                'doc_number'     => $docNumber,
                'summary'        => $summary,
                'extracted_text' => $extractedText,
                'doc_type'       => $docType,
                'status'         => $status,
                'progress'       => $progress,
                'file_path'      => $filePath,
                'file_type'      => $fileType,
                'file_size'      => $fileSize,
                'issued_date'    => $issuedDate,
                'due_date'       => $dueDate
            ];

            $newId = $docModel->create($data);
            if ($newId) {
                header("Location: document_detail.php?id={$newId}");
                exit;
            } else {
                $error = 'Không thể lưu văn bản vào CSDL!';
            }
        }
    }
}

$pageTitle = 'Thêm Văn Bản Mới';
require_once __DIR__ . '/views/header.php';
?>

<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="fw-bold mb-1">Thêm Văn Bản Mới</h3>
                <p class="text-muted mb-0">Tải lên tài liệu và tự động tạo tóm tắt nội dung bằng thuật toán Extractive TF-IDF</p>
            </div>
            <a href="documents.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Quay lại
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show mb-4">
                <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="document_add.php" enctype="multipart/form-data">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="bi bi-info-circle me-2"></i>Thông Tin Cơ Bản</h5>
                    <div class="row g-3">
                        <!-- Tiêu đề văn bản -->
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold">Tên / Tiêu đề văn bản <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="Ví dụ: Quyết định v/v Kế hoạch nâng cấp hệ thống..." required>
                        </div>

                        <!-- Số hiệu văn bản -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Số hiệu văn bản <span class="text-danger">*</span></label>
                            <input type="text" name="doc_number" class="form-control" placeholder="Ví dụ: 124/QĐ-UBND" required>
                        </div>

                        <!-- Phân loại -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Phân loại văn bản</label>
                            <select name="doc_type" class="form-select">
                                <option value="luu">Văn bản lưu (Lưu trữ / Tham khảo)</option>
                                <option value="thuc_hien">Văn bản thực hiện (Cần xử lý)</option>
                            </select>
                        </div>

                        <!-- Danh mục -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Danh mục</label>
                            <select name="category_id" class="form-select">
                                <option value="">-- Chọn danh mục --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Trạng thái -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold">Trạng thái xử lý</label>
                            <select name="status" class="form-select">
                                <option value="chua_xu_ly">Chưa xử lý</option>
                                <option value="dang_thuc_hien">Đang thực hiện</option>
                                <option value="hoan_thanh">Hoàn thành</option>
                            </select>
                        </div>

                        <!-- Ngày ban hành -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Ngày ban hành</label>
                            <input type="date" name="issued_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        </div>

                        <!-- Hạn xử lý -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold">Hạn xử lý (Nếu có)</label>
                            <input type="date" name="due_date" class="form-control">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: File Attachment & Summary -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="bi bi-paperclip me-2"></i>Đính Kèm File & Trích Yếu</h5>
                    <div class="row g-3">
                        <!-- Upload File -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Tải lên file tài liệu (.pdf, .doc, .docx - Tối đa 10MB)</label>
                            <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx">
                            <small class="text-muted d-block mt-1"><i class="bi bi-magic me-1"></i>Hệ thống sẽ tự động đọc nội dung file để trích xuất Tóm tắt bằng thuật toán Extractive TF-IDF.</small>
                        </div>

                        <!-- Trích yếu -->
                        <div class="col-12">
                            <label class="form-label fw-semibold">Trích yếu nội dung văn bản</label>
                            <textarea name="summary" class="form-control" rows="4" placeholder="Nhập tóm tắt hoặc nội dung chính của văn bản..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mb-5">
                <a href="documents.php" class="btn btn-secondary px-4">Hủy bỏ</a>
                <button type="submit" class="btn btn-primary px-4 fw-semibold">
                    <i class="bi bi-save me-1"></i> Lưu Văn Bản
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/views/footer.php'; ?>
