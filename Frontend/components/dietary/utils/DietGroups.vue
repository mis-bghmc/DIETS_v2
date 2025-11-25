<script setup>
const props = defineProps({
    dietGroup: String
});

const emit = defineEmits(['changeGroup']);

const groups = ref(['Oral', 'Enteral', 'SNS'])
</script>

<template>
    <div class="container mx-auto w-full px-0 font-bold border-b bg-inherit flex">
        <span v-for="group in groups">
            <Button text :label="group" :class="['!py-0 !px-6 !sm:text-sm !md:text-base !lg:text-lg !text-primary !rounded-none', {'!border-b-primary': dietGroup === group}]" @click="$emit('changeGroup', group)" />
        </span>
    </div>
</template>