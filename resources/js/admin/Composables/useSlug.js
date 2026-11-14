/**
 * Chuyển chuỗi bất kỳ (có dấu tiếng Việt) thành slug: chữ thường không dấu, số, gạch ngang.
 * Ví dụ: "Xe đạp địa hình Giant ATX 2024!" => "xe-dap-dia-hinh-giant-atx-2024"
 */
export function slugify(text) {
    return String(text ?? '')
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '') // bỏ dấu (tổ hợp)
        .toLowerCase()
        .replace(/đ/g, 'd') // đ không tách được bằng NFD nên xử lý riêng
        .replace(/[^a-z0-9]+/g, '-') // mọi ký tự lạ (khoảng trắng, ký hiệu, emoji...) => '-'
        .replace(/-{2,}/g, '-')
        .replace(/^-+|-+$/g, '');
}
