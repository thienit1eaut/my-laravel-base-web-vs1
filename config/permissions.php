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

        // Ví dụ khi làm module Sản phẩm sau này:
        // 'products' => [
        //     'name' => 'Sản phẩm',
        //     'actions' => [
        //         'view', 'create', 'update', 'delete',
        //         'images.view' => 'Xem hình ảnh',
        //         'images.create' => 'Thêm hình ảnh',
        //         'images.delete' => 'Xóa hình ảnh',
        //     ],
        // ],
    ],
];