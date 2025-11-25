<script setup>
import AppMenuItem from './AppMenuItem.vue';

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const model = ref(
    {
        '59': [
            {
                items: [
                    { label: 'Dashboard', icon: 'game-icons:pc', to: '/dietary' },
                    { label: 'Diet List', icon: 'game-icons:fork-knife-spoon', to: '/dietary/diet-list' },
                    { label: 'Nutrition', icon: 'game-icons:fat', to: '/dietary/nutritionally-at-risk' },
                    { label: 'Reports', icon: 'game-icons:scroll-unfurled', to: '/dietary/reports' },
                ]
            }
        ],
    }
);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model[user.user_level]" :key="item">
            <app-menu-item v-if="!item.separator" :item="item" :index="i"></app-menu-item>
            <li v-if="item.separator" class="menu-separator"></li>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>
