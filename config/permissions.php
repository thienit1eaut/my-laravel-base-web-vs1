<?php

return [
    // Nhãn của các hành động chuẩn
    'actions' => [
        'view' => 'Xem',
        'create' => 'Thêm',
        'update' => 'Sửa',
        'delete' => 'Xóa',
    ],

    // Route chỉ cần đăng nhập, không cần quyền
    'authenticated_only' => [
        'admin.dashboard',
        'admin.logout',
        'admin.profile.edit',
        'admin.profile.update',
    ],

    // Khai báo: 'tên' => hành động chuẩn (chỉ ghi tên) hoặc 'hành động riêng' => 'nhãn hiển thị'
    // Chức năng con đặt tên dạng 'chức_năng_con.hành_động', ví dụ 'images.create'
    'modules' => [
        'brands' => [
            'name' => 'Thương hiệu',
            'actions' => ['view', 'create', 'update', 'delete'],
        ],

        'article-categories' => [
            'name' => 'Danh mục bài viết',
            'actions' => ['view', 'create', 'update', 'delete'],
        ],

        'tags' => [
            'name' => 'Thẻ bài viết',
            'actions' => ['view', 'create', 'update', 'delete'],
        ],

        'articles' => [
            'name' => 'Bài viết',
            'actions' => ['view', 'create', 'update', 'delete'],
        ],

        'product-categories' => [
            'name' => 'Danh mục sản phẩm',
            'actions' => ['view', 'create', 'update', 'delete'],
        ],

        'products' => [
            'name' => 'Sản phẩm',
            'actions' => ['view', 'create', 'update', 'delete'],
        ],

        'product-tags' => [
            'name' => 'Thẻ sản phẩm',
            'actions' => ['view', 'create', 'update', 'delete'],
        ],

        'cache' => [
            'name' => 'Bộ nhớ đệm',
            'actions' => [
                'view',
                'clear-web' => 'Xóa cache trang web',
                'clear-admin' => 'Xóa cache quản trị',
            ],
        ],

        'languages' => [
            'name' => 'Ngôn ngữ',
            'actions' => [
                'view', 'create', 'update',
                'default' => 'Đặt ngôn ngữ chính',
            ],
        ],

        'accounts' => [
            'name' => 'Tài khoản',
            'actions' => ['view', 'create', 'update', 'delete'],
        ],

        'roles' => [
            'name' => 'Vai trò',
            'actions' => [
                'view', 'create', 'update', 'delete',
                'permissions.update' => 'Gán quyền cho vai trò',
            ],
        ],

        'medias' => [
            'name' => 'Thư viện ảnh',
            'actions' => [
                'view', 'create', 'update', 'delete',
                'trash.view' => 'Xem thùng rác',
                'trash.restore' => 'Khôi phục tệp từ thùng rác',
                'trash.delete' => 'Xóa vĩnh viễn tệp trong thùng rác',
                'trash.empty' => 'Dọn sạch thùng rác',
            ],
        ],
    ],
];