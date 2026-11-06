<?php
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

verify_csrf();

$fields = [
    'full_name' => trim((string) ($_POST['full_name'] ?? '')),
    'student_id' => trim((string) ($_POST['student_id'] ?? '')),
    'email' => trim((string) ($_POST['email'] ?? '')),
    'phone' => trim((string) ($_POST['phone'] ?? '')),
    'department' => trim((string) ($_POST['department'] ?? '')),
    'program' => trim((string) ($_POST['program'] ?? '')),
];

foreach ($fields as $value) {
    if ($value === '') {
        set_flash('error', 'Please complete every required field.');
        redirect('index.php');
    }
}

if (!filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Enter a valid email address.');
    redirect('index.php');
}

try {
    $photoPath = upload_student_photo($_FILES['photo'] ?? []);
    $statement = $pdo->prepare(
        'INSERT INTO students (full_name, student_id, email, phone, department, program, photo_path)
         VALUES (:full_name, :student_id, :email, :phone, :department, :program, :photo_path)'
    );
    $statement->execute($fields + ['photo_path' => $photoPath]);
    set_flash('success', 'Application submitted. Keep your student ID; an administrator will review your application.');
} catch (PDOException $exception) {
    if ($photoPath ?? null) {
        $uploadedPhoto = __DIR__ . '/' . $photoPath;
        if (is_file($uploadedPhoto) && !unlink($uploadedPhoto)) {
            error_log('Unable to remove unused uploaded photo: ' . $uploadedPhoto);
        }
    }
    if ($exception->getCode() === '23000') {
        set_flash('error', 'That student ID or email address is already registered.');
    } else {
        error_log($exception->getMessage());
        set_flash('error', 'Your application could not be saved. Please try again later.');
    }
} catch (RuntimeException $exception) {
    set_flash('error', $exception->getMessage());
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    set_flash('error', 'Your application could not be saved. Please try again later.');
}

redirect('index.php');
