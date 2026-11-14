<?php

return [
    // Bật cache nguyên trang cho phần web (nên bật khi chạy thật; tắt khi đang lập trình để thấy thay đổi ngay)
    'enabled' => env('PAGE_CACHE_ENABLED', false),

    // Thời gian tối đa giữ một trang (giây). Dữ liệu bài viết/danh mục đổi thì cache tự làm mới ngay,
    // thời gian này chỉ là lớp bảo hiểm cho các thay đổi khác (ví dụ thay ảnh trong Thư viện).
    'ttl' => env('PAGE_CACHE_TTL', 600),
];
