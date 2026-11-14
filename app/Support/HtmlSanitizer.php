<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Lọc HTML do trình soạn thảo gửi lên: chỉ giữ các thẻ và thuộc tính an toàn (danh sách trắng),
 * bỏ script, iframe, sự kiện onclick..., và link javascript:. Không dùng thư viện ngoài.
 */
class HtmlSanitizer
{
    /** Thẻ được giữ lại. */
    private const ALLOWED_TAGS = [
        'p', 'br', 'hr', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'strong', 'b', 'em', 'i', 'u', 's', 'strike', 'sub', 'sup', 'code', 'pre', 'blockquote', 'mark',
        'ul', 'ol', 'li', 'a', 'img', 'figure', 'figcaption',
        'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'span', 'div',
    ];

    /** Thẻ bị xóa cả phần ruột (không giữ lại chữ bên trong). */
    private const REMOVE_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select',
        'link', 'meta', 'base', 'svg', 'math', 'noscript', 'template', 'applet',
    ];

    /** Thuộc tính được giữ theo từng thẻ. */
    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
        'ol' => ['start'],
    ];

    /** Làm sạch HTML; trả về null nếu rỗng. */
    public static function clean(?string $html): ?string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return null;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="sanitize-root">'.$html.'</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('sanitize-root') ?? $dom->documentElement;

        if (! $root) {
            return null;
        }

        self::cleanChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        $output = trim($output);

        // Chỉ còn một đoạn trống (<p></p>) coi như rỗng
        return trim(strip_tags($output, '<img><hr><table>')) === '' ? null : $output;
    }

    /**
     * HTML để in ra trang web. Nội dung cũ lưu dạng văn bản thuần (không có thẻ) thì escape và giữ xuống dòng;
     * nội dung mới đã được lọc khi lưu nên in nguyên.
     */
    public static function render(?string $content): string
    {
        $content = (string) $content;

        if ($content === '' || $content === strip_tags($content)) {
            return nl2br(e($content));
        }

        return $content;
    }

    private static function cleanChildren(DOMNode $node): void
    {
        // Sao chép danh sách con trước vì sẽ thêm/xóa node trong lúc duyệt
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                self::cleanElement($child);
            } elseif ($child->nodeType !== XML_TEXT_NODE) {
                $node->removeChild($child); // chú thích, CDATA, lệnh xử lý...
            }
        }
    }

    private static function cleanElement(DOMElement $element): void
    {
        $tag = strtolower($element->tagName);

        if (in_array($tag, self::REMOVE_WITH_CONTENT, true)) {
            $element->parentNode->removeChild($element);

            return;
        }

        // Làm sạch phần ruột trước, rồi mới quyết định giữ hay bỏ thẻ này
        self::cleanChildren($element);

        if (! in_array($tag, self::ALLOWED_TAGS, true)) {
            // Thẻ lạ: bỏ thẻ, giữ nội dung bên trong
            while ($element->firstChild) {
                $element->parentNode->insertBefore($element->firstChild, $element);
            }
            $element->parentNode->removeChild($element);

            return;
        }

        self::cleanAttributes($element, $tag);
    }

    private static function cleanAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::ALLOWED_ATTRIBUTES[$tag] ?? [];
        $style = [];

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $name = strtolower($attribute->name);

            if ($name === 'style') {
                // Chỉ giữ vài kiểu an toàn: căn lề, màu chữ, cỡ chữ, font chữ
                $style = self::cleanStyle($attribute->value, $tag);
                $element->removeAttribute($attribute->name);

                continue;
            }

            if (! in_array($name, $allowed, true)) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($style) {
            $element->setAttribute('style', implode('; ', array_map(fn ($key, $value) => "{$key}: {$value}", array_keys($style), $style)));
        }

        if ($tag === 'a') {
            if (! self::safeUrl($element->getAttribute('href'), true)) {
                $element->removeAttribute('href');
            }

            if ($element->getAttribute('target') === '_blank') {
                $element->setAttribute('rel', 'noopener noreferrer');
            } else {
                $element->removeAttribute('target');
                $element->removeAttribute('rel');
            }
        }

        if ($tag === 'img') {
            if (! self::safeUrl($element->getAttribute('src'), false)) {
                $element->parentNode->removeChild($element);

                return;
            }

            foreach (['width', 'height'] as $size) {
                if ($element->hasAttribute($size) && ! ctype_digit($element->getAttribute($size))) {
                    $element->removeAttribute($size);
                }
            }
        }
    }

    /**
     * Lọc thuộc tính style: text-align (thẻ khối), color / font-size / font-family (thẻ span).
     * Giá trị phải khớp mẫu nghiêm ngặt, không nhận url(), expression... Trả về [tên => giá trị].
     *
     * @return array<string, string>
     */
    private static function cleanStyle(string $style, string $tag): array
    {
        $blocks = ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'li', 'th', 'td', 'div', 'blockquote'];
        $result = [];

        foreach (explode(';', $style) as $declaration) {
            [$name, $value] = array_pad(explode(':', $declaration, 2), 2, '');
            $name = strtolower(trim($name));
            $value = trim($value);

            if ($name === 'text-align' && in_array($tag, $blocks, true) && preg_match('/^(left|center|right|justify)$/i', $value)) {
                $result[$name] = strtolower($value);
            } elseif ($tag === 'span') {
                if ($name === 'color' && (
                    preg_match('/^#[0-9a-f]{3,8}$/i', $value)
                    || preg_match('/^rgba?\(\s*\d{1,3}\s*,\s*\d{1,3}\s*,\s*\d{1,3}\s*(,\s*[0-9.]+\s*)?\)$/i', $value)
                    || preg_match('/^[a-z]{3,20}$/i', $value)
                )) {
                    $result[$name] = $value;
                } elseif ($name === 'font-size' && preg_match('/^\d{1,3}(\.\d{1,2})?(px|em|rem|%)$/i', $value)) {
                    $result[$name] = $value;
                } elseif ($name === 'font-family' && preg_match('/^[\w\s,"\'-]{1,120}$/u', $value)) {
                    $result[$name] = $value;
                }
            }
        }

        return $result;
    }

    // Link/ảnh chỉ nhận đường dẫn tương đối hoặc http(s) (link còn nhận mailto, tel, #mục)
    private static function safeUrl(string $url, bool $isLink): bool
    {
        $url = trim(preg_replace('/[\x00-\x20]+/', '', $url) ?? '');

        if ($url === '') {
            return false;
        }

        if (str_starts_with($url, '//')) {
            return false;
        }

        if (str_starts_with($url, '/') || ($isLink && str_starts_with($url, '#'))) {
            return true;
        }

        $pattern = $isLink ? '#^(https?:|mailto:|tel:)#i' : '#^https?:#i';

        return (bool) preg_match($pattern, $url);
    }
}
