<script setup>
import Button from '../UI/Button.vue';

// Bảng thông số kỹ thuật dạng "tên - giá trị". Giá trị v-model là mảng [{ name, value }].
// "errors" là form.errors để hiện lỗi từng dòng (khóa dạng specs.0.name).
// Dùng: <SpecsField v-model="form.specs" :errors="form.errors" />
defineProps({
    label: String,
    errors: { type: Object, default: () => ({}) },
    max: { type: Number, default: 50 },
});

const model = defineModel({ type: Array, default: () => [] });

const add = () => (model.value = [...model.value, { name: '', value: '' }]);
const remove = (index) => (model.value = model.value.filter((_, i) => i !== index));
const move = (index, step) => {
    const target = index + step;
    if (target < 0 || target >= model.value.length) return;

    const rows = [...model.value];
    [rows[index], rows[target]] = [rows[target], rows[index]];
    model.value = rows;
};

const inputClass = (error) => [
    'block w-full rounded-[4px] border bg-white px-2 py-1 text-[14px] leading-5 text-slate-800 focus:outline-none focus:ring-1',
    error ? 'border-red-500 focus:ring-red-500' : 'border-[#8c8f94] focus:border-indigo-600 focus:ring-indigo-600',
];
</script>

<template>
    <div>
        <span v-if="label" class="block text-[14px] font-semibold text-slate-800">{{ label }}</span>

        <div class="mt-1 space-y-2">
            <div v-for="(row, index) in model" :key="index">
                <div class="flex items-center gap-2">
                    <input v-model="row.name" type="text" maxlength="100" placeholder="Tên thông số (vd: Khung)" :class="inputClass(errors[`specs.${index}.name`])" />
                    <input v-model="row.value" type="text" maxlength="500" placeholder="Giá trị (vd: Nhôm 6061)" :class="inputClass(errors[`specs.${index}.value`])" />
                    <button type="button" class="px-1 text-slate-600 hover:text-indigo-700 disabled:opacity-30" :disabled="index === 0" title="Lên" @click="move(index, -1)">&uarr;</button>
                    <button type="button" class="px-1 text-slate-600 hover:text-indigo-700 disabled:opacity-30" :disabled="index === model.length - 1" title="Xuống" @click="move(index, 1)">&darr;</button>
                    <button type="button" class="px-1 text-red-600 hover:text-red-800" title="Xóa dòng" @click="remove(index)">&times;</button>
                </div>
                <p v-if="errors[`specs.${index}.name`] || errors[`specs.${index}.value`]" class="mt-0.5 text-xs text-red-600">
                    {{ errors[`specs.${index}.name`] || errors[`specs.${index}.value`] }}
                </p>
            </div>
        </div>

        <div class="mt-2">
            <Button variant="secondary" :disabled="model.length >= max" @click="add">Thêm thông số</Button>
        </div>
        <p v-if="errors.specs" class="mt-1 text-xs text-red-600">{{ errors.specs }}</p>
    </div>
</template>
