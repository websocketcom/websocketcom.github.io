
<?php

header('Content-Type: application/json; charset=utf-8');
try {
    if (
        !isset($_FILES['uploadfile']['error']) ||
        is_array($_FILES['uploadfile']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }
    switch ($_FILES['uploadfile']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }
    if ($_FILES['uploadfile']['size'] > 1000000) {
        throw new RuntimeException('Exceeded filesize limit.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($_FILES['uploadfile']['tmp_name']),
        array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ),
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }
    $uploadFileName = sprintf('/uploads/%s.%s',sha1_file($_FILES['uploadfile']['tmp_name']),$ext);
    $uploadfilename = $_POST['uploadfilename'];
    if(!empty($uploadfilename)) $uploadFileName = "/uploads/{$uploadfilename}";
    if (!move_uploaded_file($_FILES['uploadfile']['tmp_name'],".{$uploadFileName}")) {
        throw new RuntimeException('Failed to move uploaded file.');
    }
    echo json_encode([
                 "code" => 200,
                 "data" => ["url"=>"https://static.boolcdn.net{$uploadFileName}"]
             ]);
} catch (RuntimeException $e) {
    echo json_encode([
                          "code" => 201,
                          "message" => $e->getMessage()
                      ]);
}

?>