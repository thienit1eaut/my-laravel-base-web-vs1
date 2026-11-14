<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import StarterKit from '@tiptap/starter-kit';
import Image from '@tiptap/extension-image';
import TextAlign from '@tiptap/extension-text-align';
import { Table, TableRow, TableCell, TableHeader } from '@tiptap/extension-table';
import { TextStyle, Color, FontFamily, FontSize } from '@tiptap/extension-text-style';
import MediaPicker from '../UI/MediaPicker.vue';
import { usePermission } from '../../Composables/usePermission.js';

// Trình soạn thảo văn bản (Tiptap): tiêu đề h1-h6, đậm/nghiêng/gạch chân/gạch ngang, căn lề, danh sách,
// trích dẫn, link, đường kẻ, chèn ảnh từ Thư viện ảnh, xem/sửa mã HTML. Giá trị v-model là chuỗi HTML.
// Server luôn lọc lại HTML (App\Support\HtmlSanitizer) trước khi lưu.
// Dùng: <RichTextField v-model="form.content" label="Nội dung" :error="form.errors.content" />
const props = defineProps({
    label: String,
    error: String,
    hint: String,
    minHeight: { type: String, default: '320px' },
});

const model = defineModel({ type: String, default: '' });

const { can } = usePermission();
const picking = ref(false);
const sourceMode = ref(false);

const editor = useEditor({
    content: model.value ?? '',
    extensions: [
        StarterKit.configure({
            heading: { levels: [1, 2, 3, 4, 5, 6] },
            link: { openOnClick: false, HTMLAttributes: { rel: 'noopener noreferrer' } },
        }),
        Image,
        TextAlign.configure({ types: ['heading', 'paragraph'] }),
        TextStyle,
        Color,
        FontFamily,
        FontSize,
        Table.configure({ resizable: false }),
        TableRow,
        TableHeader,
        TableCell,
    ],
    editorProps: {
        attributes: { class: 'rich-content focus:outline-none px-3 py-2 text-[15px] text-slate-800' },
    },
    onUpdate: ({ editor }) => {
        model.value = editor.isEmpty ? '' : editor.getHTML();
    },
});

// Giá trị đổi từ bên ngoài (vd: reset form) thì cập nhật lại nội dung ô soạn thảo
watch(model, (value) => {
    if (editor.value && (value ?? '') !== (editor.value.isEmpty ? '' : editor.value.getHTML())) {
        editor.value.commands.setContent(value ?? '', { emitUpdate: false });
    }
});

onBeforeUnmount(() => editor.value?.destroy());

const active = (name, attrs) => editor.value?.isActive(name, attrs) ?? false;
const chain = () => editor.value.chain().focus();

const blockType = computed(() => {
    for (let level = 1; level <= 6; level++) {
        if (active('heading', { level })) return `h${level}`;
    }
    return 'p';
});

const setBlock = (event) => {
    const value = event.target.value;
    if (value === 'p') chain().setParagraph().run();
    else chain().setHeading({ level: Number(value[1]) }).run();
};

const setLink = () => {
    const previous = editor.value.getAttributes('link').href ?? '';
    const url = window.prompt('Nhập đường dẫn (để trống để bỏ link):', previous);

    if (url === null) return;
    if (url.trim() === '') chain().extendMarkRange('link').unsetLink().run();
    else chain().extendMarkRange('link').setLink({ href: url.trim() }).run();
};

const insertImage = (media) => {
    picking.value = false;
    chain().setImage({ src: media.url, alt: media.alt_text ?? media.name ?? '' }).run();
};

const toggleSource = () => {
    sourceMode.value = !sourceMode.value;
    // Thoát chế độ mã HTML thì nạp lại nội dung đã sửa vào ô soạn thảo
    if (!sourceMode.value) editor.value.commands.setContent(model.value ?? '', { emitUpdate: false });
};

// Font chữ và cỡ chữ gợi ý (cỡ chữ để trống = cỡ mặc định)
const fonts = [
    { label: 'Mặc định', value: '' },
    { label: 'Arial', value: 'Arial, Helvetica, sans-serif' },
    { label: 'Tahoma', value: 'Tahoma, Geneva, sans-serif' },
    { label: 'Verdana', value: 'Verdana, Geneva, sans-serif' },
    { label: 'Times New Roman', value: '"Times New Roman", Times, serif' },
    { label: 'Georgia', value: 'Georgia, serif' },
    { label: 'Courier New', value: '"Courier New", Courier, monospace' },
];
const sizes = ['12px', '14px', '16px', '18px', '20px', '24px', '28px', '32px', '40px'];

const currentFont = computed(() => editor.value?.getAttributes('textStyle').fontFamily ?? '');
const currentSize = computed(() => editor.value?.getAttributes('textStyle').fontSize ?? '');
const currentColor = computed(() => editor.value?.getAttributes('textStyle').color ?? '#000000');

const setFont = (event) => {
    const value = event.target.value;
    if (value) chain().setFontFamily(value).run();
    else chain().unsetFontFamily().run();
};

const setSize = (event) => {
    const value = event.target.value;
    if (value) chain().setFontSize(value).run();
    else chain().unsetFontSize().run();
};

const setColor = (event) => chain().setColor(event.target.value).run();

const inTable = computed(() => active('table'));

const buttonClass = (isActive) => [
    'inline-flex h-8 min-w-8 items-center justify-center rounded px-1.5 text-[14px] text-slate-700 hover:bg-slate-200 disabled:opacity-40',
    isActive ? 'bg-indigo-100 text-indigo-700' : '',
];
</script>

<template>
    <div>
        <span v-if="label" class="block text-[14px] font-semibold text-slate-800">{{ label }}</span>

        <div
            class="mt-1 overflow-hidden rounded-[4px] border bg-white"
            :class="error ? 'border-red-500' : 'border-[#8c8f94]'"
        >
            <div v-if="editor" class="flex flex-wrap items-center gap-0.5 border-b border-slate-300 bg-slate-50 p-1">
                <select
                    :value="blockType"
                    :disabled="sourceMode"
                    class="h-8 rounded border border-slate-300 bg-white px-1 text-[14px]"
                    @change="setBlock"
                >
                    <option value="p">Đoạn văn</option>
                    <option v-for="n in 6" :key="n" :value="`h${n}`">Tiêu đề {{ n }}</option>
                </select>

                <select
                    :value="currentFont"
                    :disabled="sourceMode"
                    title="Font chữ"
                    class="h-8 w-28 rounded border border-slate-300 bg-white px-1 text-[14px]"
                    @change="setFont"
                >
                    <option v-for="font in fonts" :key="font.label" :value="font.value">{{ font.label }}</option>
                </select>
                <select
                    :value="currentSize"
                    :disabled="sourceMode"
                    title="Cỡ chữ"
                    class="h-8 w-20 rounded border border-slate-300 bg-white px-1 text-[14px]"
                    @change="setSize"
                >
                    <option value="">Cỡ chữ</option>
                    <option v-for="size in sizes" :key="size" :value="size">{{ size }}</option>
                </select>
                <input
                    type="color"
                    :value="currentColor"
                    :disabled="sourceMode"
                    title="Màu chữ"
                    class="h-8 w-8 cursor-pointer rounded border border-slate-300 bg-white p-0.5"
                    @input="setColor"
                />
                <button type="button" title="Bỏ màu chữ" :disabled="sourceMode" :class="buttonClass(false)" @click="chain().unsetColor().run()">A&#215;</button>

                <span class="mx-1 h-5 w-px bg-slate-300" />

                <button type="button" title="Đậm" :disabled="sourceMode" :class="buttonClass(active('bold'))" @click="chain().toggleBold().run()"><b>B</b></button>
                <button type="button" title="Nghiêng" :disabled="sourceMode" :class="buttonClass(active('italic'))" @click="chain().toggleItalic().run()"><i>I</i></button>
                <button type="button" title="Gạch chân" :disabled="sourceMode" :class="buttonClass(active('underline'))" @click="chain().toggleUnderline().run()"><u>U</u></button>
                <button type="button" title="Gạch ngang" :disabled="sourceMode" :class="buttonClass(active('strike'))" @click="chain().toggleStrike().run()"><s>S</s></button>

                <span class="mx-1 h-5 w-px bg-slate-300" />

                <button type="button" title="Căn trái" :disabled="sourceMode" :class="buttonClass(active({ textAlign: 'left' }))" @click="chain().setTextAlign('left').run()">&#8676;</button>
                <button type="button" title="Căn giữa" :disabled="sourceMode" :class="buttonClass(active({ textAlign: 'center' }))" @click="chain().setTextAlign('center').run()">&#8596;</button>
                <button type="button" title="Căn phải" :disabled="sourceMode" :class="buttonClass(active({ textAlign: 'right' }))" @click="chain().setTextAlign('right').run()">&#8677;</button>
                <button type="button" title="Căn đều" :disabled="sourceMode" :class="buttonClass(active({ textAlign: 'justify' }))" @click="chain().setTextAlign('justify').run()">&#9776;</button>

                <span class="mx-1 h-5 w-px bg-slate-300" />

                <button type="button" title="Danh sách chấm" :disabled="sourceMode" :class="buttonClass(active('bulletList'))" @click="chain().toggleBulletList().run()">&bull;&#8801;</button>
                <button type="button" title="Danh sách số" :disabled="sourceMode" :class="buttonClass(active('orderedList'))" @click="chain().toggleOrderedList().run()">1.&#8801;</button>
                <button type="button" title="Trích dẫn" :disabled="sourceMode" :class="buttonClass(active('blockquote'))" @click="chain().toggleBlockquote().run()">&ldquo;</button>
                <button type="button" title="Khối mã" :disabled="sourceMode" :class="buttonClass(active('codeBlock'))" @click="chain().toggleCodeBlock().run()">&lt;/&gt;</button>
                <button type="button" title="Đường kẻ ngang" :disabled="sourceMode" :class="buttonClass(false)" @click="chain().setHorizontalRule().run()">&mdash;</button>

                <span class="mx-1 h-5 w-px bg-slate-300" />

                <button type="button" title="Chèn bảng 3x3" :disabled="sourceMode" :class="buttonClass(inTable)" @click="chain().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run()">Bảng</button>
                <template v-if="inTable && !sourceMode">
                    <button type="button" title="Thêm cột bên phải" :class="buttonClass(false)" @click="chain().addColumnAfter().run()">+Cột</button>
                    <button type="button" title="Xóa cột" :class="buttonClass(false)" @click="chain().deleteColumn().run()">-Cột</button>
                    <button type="button" title="Thêm dòng bên dưới" :class="buttonClass(false)" @click="chain().addRowAfter().run()">+Dòng</button>
                    <button type="button" title="Xóa dòng" :class="buttonClass(false)" @click="chain().deleteRow().run()">-Dòng</button>
                    <button type="button" title="Bật/tắt dòng tiêu đề" :class="buttonClass(false)" @click="chain().toggleHeaderRow().run()">Tiêu đề</button>
                    <button type="button" title="Xóa bảng" :class="buttonClass(false)" @click="chain().deleteTable().run()">Xóa bảng</button>
                </template>

                <span class="mx-1 h-5 w-px bg-slate-300" />

                <button type="button" title="Chèn/sửa link" :disabled="sourceMode" :class="buttonClass(active('link'))" @click="setLink">Link</button>
                <button
                    v-if="can('medias.view')"
                    type="button"
                    title="Chèn ảnh từ Thư viện ảnh"
                    :disabled="sourceMode"
                    :class="buttonClass(false)"
                    @click="picking = true"
                >
                    Ảnh
                </button>
                <button type="button" title="Xóa định dạng" :disabled="sourceMode" :class="buttonClass(false)" @click="chain().unsetAllMarks().clearNodes().run()">Tx</button>

                <span class="mx-1 h-5 w-px bg-slate-300" />

                <button type="button" title="Hoàn tác" :disabled="sourceMode || !editor.can().undo()" :class="buttonClass(false)" @click="chain().undo().run()">&#8630;</button>
                <button type="button" title="Làm lại" :disabled="sourceMode || !editor.can().redo()" :class="buttonClass(false)" @click="chain().redo().run()">&#8631;</button>

                <button type="button" title="Xem/sửa mã HTML" :class="[buttonClass(sourceMode), 'ml-auto']" @click="toggleSource">&lt;html&gt;</button>
            </div>

            <textarea
                v-if="sourceMode"
                v-model="model"
                spellcheck="false"
                class="block w-full border-0 px-3 py-2 font-mono text-[13px] text-slate-800 focus:outline-none focus:ring-0"
                :style="{ minHeight }"
            />
            <EditorContent v-else :editor="editor" :style="{ minHeight }" class="cursor-text" @click="editor?.commands.focus()" />
        </div>

        <p v-if="hint && !error" class="mt-1 text-xs text-slate-500">{{ hint }}</p>
        <p v-if="error" class="mt-1 text-xs text-red-600">{{ error }}</p>

        <MediaPicker :show="picking" @close="picking = false" @select="insertImage" />
    </div>
</template>
