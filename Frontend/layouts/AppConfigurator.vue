<script setup>
const { layoutConfig, isDarkTheme } = useLayout();
const { primaryColors, surfaces, updateColors }  = useAppConfigurator();
</script>

<template>
    <div class="flex flex-col gap-4">
        <div>
            <span class="text-sm text-muted-color font-semibold">Primary</span>
            <div class="pt-2 flex gap-2 flex-wrap justify-between">
                <button
                    v-for="primaryColor of primaryColors"
                    :key="primaryColor.name"
                    type="button"
                    :title="primaryColor.name"
                    @click="updateColors('primary', primaryColor)"
                    :class="['border-none w-5 h-5 rounded-full p-0 cursor-pointer outline-none outline-offset-1', { 'outline-primary': layoutConfig.primary === primaryColor.name }]"
                    :style="{ backgroundColor: `${primaryColor.name === 'noir' ? 'var(--text-color)' : primaryColor.palette['500']}` }"
                ></button>
            </div>
        </div>
        <div>
            <span class="text-sm text-muted-color font-semibold">Surface</span>
            <div class="pt-2 flex gap-2 flex-wrap justify-between">
                <button
                    v-for="surface of surfaces"
                    :key="surface.name"
                    type="button"
                    :title="surface.name"
                    @click="updateColors('surface', surface)"
                    :class="[
                        'border-none w-5 h-5 rounded-full p-0 cursor-pointer outline-none outline-offset-1',
                        { 'outline-primary': layoutConfig.surface ? layoutConfig.surface === surface.name : isDarkTheme ? surface.name === 'zinc' : surface.name === 'slate' }
                    ]"
                    :style="{ backgroundColor: `${surface.palette['500']}` }"
                ></button>
            </div>
        </div>
    </div>
</template>
