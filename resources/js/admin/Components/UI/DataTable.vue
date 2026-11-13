<script setup>
import { computed, useSlots, watch } from 'vue';

const props = defineProps({
    // [{ key: 'name', label: 'Tên', class: 'font-medium' }, ...]
    columns: { type: Array, required: true },
    rows: { type: Array, default: () => [] },
    rowKey: { type: String, default: 'id' },
    selectable: { type: Boolean, default: true },
    emptyText: { type: String, default: 'Chưa có dữ liệu.' },
});

// Danh sách khóa (id) các dòng đang được chọn: v-model:selected="..."
const selected = defineModel('selected', { type: Array, default: () => [] });

const slots = useSlots();

const colspan = computed(
    () => props.columns.length + (props.selectable ? 1 : 0) + (slots.actions ? 1 : 0),
);

const allChecked = computed(
    () => props.rows.length > 0 && props.rows.every((row) => selected.value.includes(row[props.rowKey])),
);
const someChecked = computed(() => selected.value.length > 0 && !allChecked.value);

const toggleAll = (event) => {
    selected.value = event.target.checked ? props.rows.map((row) => row[props.rowKey]) : [];
};

// Khi dữ liệu đổi (chuyển trang, tìm kiếm), bỏ các dòng đã chọn không còn hiển thị
watch(
    () => props.rows,
    (rows) => {
        const keys = new Set(rows.map((row) => row[props.rowKey]));
        const kept = selected.value.filter((key) => keys.has(key));

        if (kept.length !== selected.value.length) {
            selected.value = kept;
        }
    },
);
</script>

<template>
    <div>
        <!-- Thanh thao tác hàng loạt: chỉ hiện khi có dòng được chọn -->
        <div
            v-if="selectable && selected.length > 0"
            class="mb-2 flex flex-wrap items-center gap-3 rounded border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-[13px] text-indigo-800"
        >
            <span>Đã chọn <strong>{{ selected.length }}</strong> dòng</span>

            <div class="flex items-center gap-2">
                <slot name="bulk" :selected="selected" />
            </div>

            <button type="button" class="ml-auto hover:underline" @click="selected = []">Bỏ chọn</button>
        </div>

        <div class="overflow-x-auto rounded border border-slate-300 bg-white shadow-[0_1px_1px_rgba(0,0,0,0.04)]">
            <table class="w-full text-left text-[13px]">
                <thead class="border-b border-slate-300 text-slate-800">
                    <tr>
                        <th v-if="selectable" class="w-9 px-2.5 py-2">
                            <input
                                type="checkbox"
                                aria-label="Chọn tất cả"
                                :checked="allChecked"
                                :indeterminate="someChecked"
                                @change="toggleAll"
                            />
                        </th>
                        <th v-for="column in columns" :key="column.key" class="px-2.5 py-2 font-semibold">
                            {{ column.label }}
                        </th>
                        <th v-if="$slots.actions" class="px-2.5 py-2 text-right font-semibold">Thao tác</th>
                    </tr>
                </thead>

                <tbody>
                    <tr
                        v-for="row in rows"
                        :key="row[rowKey]"
                        :class="selected.includes(row[rowKey]) ? 'bg-indigo-50 even:bg-indigo-50' : 'even:bg-slate-50'"
                    >
                        <td v-if="selectable" class="w-9 px-2.5 py-2">
                            <input
                                v-model="selected"
                                type="checkbox"
                                aria-label="Chọn dòng"
                                :value="row[rowKey]"
                            />
                        </td>

                        <td v-for="column in columns" :key="column.key" class="px-2.5 py-2" :class="column.class">
                            <!-- Tùy biến ô: <template #cell-ten_cot="{ row, value }"> -->
                            <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                                {{ row[column.key] }}
                            </slot>
                        </td>

                        <td v-if="$slots.actions" class="px-2.5 py-2 text-right">
                            <slot name="actions" :row="row" />
                        </td>
                    </tr>

                    <tr v-if="rows.length === 0">
                        <td :colspan="colspan" class="px-2.5 py-8 text-center text-slate-500">
                            {{ emptyText }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>