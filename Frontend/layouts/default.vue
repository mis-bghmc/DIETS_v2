<script setup>
import AppLayout from './AppLayout.vue';

const route = useRoute();

const dietListStore = useDietListStore();
const { data, error, status, refresh } = await useLazyAsyncData('diet-list', () => dietListStore.getDietListLatest(), { default : () => ({}) } );

provide('diet_list', {data, error, status});

onMounted(() => {
    if(route.name?.includes('dietary')) refresh();
});
</script>

<template>
    <AppLayout />
</template>