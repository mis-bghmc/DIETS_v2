<script setup>
import DIETS_logo from '~/public/images/DIETS_logo.svg';
</script>

<template>
    <div class="container mx-auto">
        <div class="flex items-center h-full md:h-[77vh]">
            <div class="grid grid-cols-12 gap-2 items-center w-full">
                <div class="col-span-12 lg:col-span-6">
                    <DIETS_logo class="text-primary w-full h-full lg:h-[77vh]" />
                </div>
                
                <div class="col-span-12 lg:col-span-6">
                    <!-- <div class="flex md:hidden w-full justify-center">
                        <div class="[writing-mode:vertical-rl] [text-orientation:upright] flex items-center">
                            <span class="text-primary text-7xl font-bold">D</span>
                            <span class="text-3xl font-semibold">ietary</span>
                        </div>
                        <div class="[writing-mode:vertical-rl] [text-orientation:upright] flex items-center">
                            <span class="text-primary text-7xl font-bold">I</span>
                            <span class="text-3xl font-semibold">nformation &amp</span>
                        </div>
                        <div class="[writing-mode:vertical-rl] [text-orientation:upright] flex items-center">
                            <span class="text-primary text-7xl font-bold">E</span>
                            <span class="text-3xl font-semibold">ngagement</span>
                        </div>
                        <div class="[writing-mode:vertical-rl] [text-orientation:upright] flex items-center">
                            <span class="text-primary text-7xl font-bold">T</span>
                            <span class="text-3xl font-semibold">echnology</span>
                        </div>
                        <div class="[writing-mode:vertical-rl] [text-orientation:upright] flex items-center">
                            <span class="text-primary text-7xl font-bold">S</span>
                            <span class="text-3xl font-semibold">ystem</span>
                        </div>
                    </div> -->

                    <div class="flex md:hidden">
                        <Divider layout="vertical" class="border border-primary" />
                        
                        <div class="flex flex-col gap-2">
                            <div class="grid grid-cols-6 items-end">
                                <span class="col-span-1 text-primary text-7xl font-bold text-center">D</span>
                                <span class="col-span-5 text-3xl font-semibold mb-1">ietary</span>
                            </div>
                            <div class="grid grid-cols-6 items-end">
                                <span class="col-span-1 text-primary text-7xl font-bold text-center">I</span>
                                <span class="col-span-5 text-3xl font-semibold mb-1">nformation and</span>
                            </div>
                            <div class="grid grid-cols-6 items-end">
                                <span class="col-span-1 text-primary text-7xl font-bold text-center">E</span>
                                <span class="col-span-5 text-3xl font-semibold mb-1">ngagement</span>
                            </div>
                            <div class="grid grid-cols-6 items-end">
                                <span class="col-span-1 text-primary text-7xl font-bold text-center">T</span>
                                <span class="col-span-5 text-3xl font-semibold mb-1">echnology</span>
                            </div>
                            <div class="grid grid-cols-6 items-end">
                                <span class="col-span-1 text-primary text-7xl font-bold text-center">S</span>
                                <span class="col-span-5 text-3xl font-semibold mb-1">ystem</span>
                            </div>
                        </div>
                    </div>

                    <div class="hidden md:flex justify-center lg:justify-start">
                        <div class="flex md:inline-block flex-col gap-2">
                            <div class="w-full lg:w-11/12 flex justify-between">
                                <span class="text-primary text-7xl font-bold">D</span>
                                <span class="text-primary text-7xl font-bold">I</span>
                                <span class="text-primary text-7xl font-bold">E</span>
                                <span class="text-primary text-7xl font-bold">T</span>
                                <span class="text-primary text-7xl font-bold">S</span>
                            </div>
            
                            <div class="w-full flex justify-center lg:justify-start">
                                <span class="text-base md:text-xl font-semibold">Dietary Information and Engagement Technology System</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>