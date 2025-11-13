<?php

// CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: *');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Max-Age: 86400');

$filepaths = array_filter(explode(',', $_GET['filepaths']));

if (isset($_GET['find_log_files'])) {
    $filepaths = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator('.'));
    foreach ($iterator as $file) {
        if ($file->isFile() && preg_match('/\.log$/', $file->getFilename())) {
            $filepaths[] = $file->getPathname();
        }
    }
    header('Content-Type: application/json');
    echo json_encode($filepaths, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
} else if ($_GET['filepath']) {
    if (file_exists($_GET['filepath'])) {
        header('Content-Type: text/plain');
        echo file_get_contents($_GET['filepath']);
    } else {
        http_response_code(404);
    }
} else if ($filepaths) {
    if (!is_dir('priv')) {
        mkdir('priv');
    }

    $result = [];
    foreach ($filepaths as $filepath) {
        $prev_filepath = 'priv/' . md5($filepath);
        if (file_exists($prev_filepath)) {
            $filesize = filesize($filepath);
            $priv_filesize = filesize($prev_filepath);
            if ($filesize !== $priv_filesize) {
                copy($filepath, $prev_filepath);
                $result[] = [
                    'filepath' => $filepath,
                    'contents' => substr(file_get_contents($filepath), $priv_filesize),
                    'modified_time' => filemtime($filepath)
                ];
            }
        } else if (file_exists($filepath)) {
            copy($filepath, $prev_filepath);
            $result[] = [
                'filepath' => $filepath,
                'contents' => file_get_contents($filepath),
                'modified_time' => filemtime($filepath)
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
} else {
    
}
