<?php

return [
    // Mỗi loại: nhãn, danh sách MIME được phép (MIME => đuôi tệp lưu trên đĩa), dung lượng tối đa (KB)
    'types' => [
        'image' => [
            'label' => 'Ảnh',
            'mimes' => [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif',
            ],
            'max_kb' => 10240, // 10 MB
        ],
        'document' => [
            'label' => 'Tài liệu',
            'mimes' => ['application/pdf' => 'pdf'],
            'max_kb' => 20480, // 20 MB
        ],
        'audio' => [
            'label' => 'Âm thanh',
            'mimes' => [
                'audio/mpeg' => 'mp3',
                'audio/mp3' => 'mp3',
                'audio/mp4' => 'm4a',
                'audio/x-m4a' => 'm4a',
            ],
            'max_kb' => 25600, // 25 MB
        ],
        'video' => [
            'label' => 'Video',
            'mimes' => [
                'video/mp4' => 'mp4',
                'video/webm' => 'webm',
            ],
            'max_kb' => 102400, // 100 MB
        ],
    ],

    // Bỏ qua tạo thumbnail/webp cho ảnh quá lớn (GD cần nhiều RAM: khoảng 4 byte mỗi điểm ảnh)
    'max_pixels' => 30000000,

    'jpeg_quality' => 82,
    'webp_quality' => 80,
];