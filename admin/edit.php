<?php
require_once __DIR__ . '/../functions.php';
require_admin();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('Invalid application.');
}

$statement = $pdo->prepare('SELECT * FROM students WHERE id = ?');
$statement->execute([$id]);
$student = $statement->fetch();
if (!$student) {
    http_response_code(404);
    exit('Application not found.');
}

$fields = [
    'full_name' => $student['full_name'],
    'student_id' => $student['student_id'],
    'email' => $student['email'],
    'phone' => $student['phone'],
    'department' => $student['department'],
    'program' => $student['program'],
];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    foreach ($fields as $field => $originalValue) {
        $fields[$field] = trim((string) ($_POST[$field] ?? ''));
    }

    foreach ($fields as $value) {
        if ($value === '') {
            $error = 'Please complete every required field.';
            break;
        }
    }

    if ($error === '' && !filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Enter a valid email address.';
    }

    $newPhotoPath = null;
    $removePhoto = isset($_POST['remove_photo']);

    if ($error === '') {
        try {
            $photoFile = $_FILES['photo'] ?? [];
            if (($photoFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $newPhotoPath = upload_student_photo($photoFile);
            }

            $photoPath = $newPhotoPath ?? ($removePhoto ? null : $student['photo_path']);
            $update = $pdo->prepare(
                'UPDATE students
                 SET full_name = :full_name, student_id = :student_id, email = :email,
                     phone = :phone, department = :department, program = :program, photo_path = :photo_path
                 WHERE id = :id'
            );
            $update->execute($fields + ['photo_path' => $photoPath, 'id' => $id]);

            if ($student['photo_path'] && $student['photo_path'] !== $photoPath) {
                $oldPhoto = __DIR__ . '/../' . $student['photo_path'];
                if (is_file($oldPhoto) && !unlink($oldPhoto)) {
                    error_log('Unable to remove replaced student photo: ' . $oldPhoto);
                }
            }

            set_flash('success', 'Student details updated.');
            redirect('review.php?id=' . $id);
        } catch (RuntimeException $exception) {
            $error = $exception->getMessage();
        } catch (PDOException $exception) {
            if ($newPhotoPath !== null) {
                $uploadedPhoto = __DIR__ . '/../' . $newPhotoPath;
                if (is_file($uploadedPhoto) && !unlink($uploadedPhoto)) {
                    error_log('Unable to remove unused uploaded photo: ' . $uploadedPhoto);
                }
            }
            if ($exception->getCode() === '23000') {
                $error = 'That student ID or email address is already used by another application.';
            } else {
                error_log($exception->getMessage());
                $error = 'The student details could not be updated. Please try again.';
            }
        } catch (Throwable $exception) {
            if ($newPhotoPath !== null) {
                $uploadedPhoto = __DIR__ . '/../' . $newPhotoPath;
                if (is_file($uploadedPhoto) && !unlink($uploadedPhoto)) {
                    error_log('Unable to remove unused uploaded photo: ' . $uploadedPhoto);
                }
            }
            error_log($exception->getMessage());
            $error = 'The student details could not be updated. Please try again.';
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit student details</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
<header class="site-header">
    <a class="brand" href="index.php">Digital Identity Admin</a>
    <a class="header-link" href="review.php?id=<?= (int) $student['id'] ?>">Back to application</a>
</header>
<main class="page-shell narrow">
    <section class="panel">
        <p class="eyebrow">EDIT APPLICATION</p>
        <h1>Correct student details</h1>
        <p>Changes are reflected on the identity card and its verification page.</p>
        <?php if ($error !== ''): ?><div class="notice error"><?= h($error) ?></div><?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <label>Full name
                <input type="text" name="full_name" maxlength="150" value="<?= h($fields['full_name']) ?>" required>
            </label>
            <label>Student ID
                <input type="text" name="student_id" maxlength="60" value="<?= h($fields['student_id']) ?>" required>
            </label>
            <label>Email address
                <input type="email" name="email" maxlength="190" value="<?= h($fields['email']) ?>" required>
            </label>
            <label>Phone number
                <input type="tel" name="phone" maxlength="30" value="<?= h($fields['phone']) ?>" required>
            </label>
            <label>Department
                <input type="text" name="department" maxlength="120" value="<?= h($fields['department']) ?>" required>
            </label>
            <label>Program / degree
                <input type="text" name="program" maxlength="120" value="<?= h($fields['program']) ?>" required>
            </label>
            <?php if ($student['photo_path']): ?>
                <div class="current-photo">
                    <img class="student-photo" src="../<?= h($student['photo_path']) ?>" alt="Current student photo">
                    <label class="checkbox-label"><input type="checkbox" name="remove_photo" value="1"> Remove current photo</label>
                </div>
            <?php endif; ?>
            <label>Replace photo (optional, JPG/PNG/WebP; max 2 MB)
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp">
            </label>
            <div class="review-actions">
                <button type="submit">Save corrections</button>
                <a class="button secondary" href="review.php?id=<?= (int) $student['id'] ?>">Cancel</a>
            </div>
        </form>
    </section>
</main>
</body>
</html>
