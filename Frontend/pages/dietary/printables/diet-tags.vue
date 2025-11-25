<script setup>
import { MealsService } from '~/services/MealsService';

definePageMeta({
    layout: 'printables'
});

const route = useRoute();

const { formatMonthShort } = useDate();

const diet_group = ref(route.query.group);
const print_option = ref(route.query.option);
const wards = ref(route.query.wards);

const meal_times = ref({
    BREAKFAST: {
        before_time: '10:00 AM',
        bg_color: '#fb7185'
    },
    LUNCH: {
        before_time: '03:00 PM',
        bg_color: '#60a5fa'
    },
    DINNER: {
        before_time: '07:00 PM',
        bg_color: '#34d399'
    },
    AM: {
        bg_color: '#fcd34d'
    },
    PM: {
        bg_color: '#fb923c'
    },
    MN: {
        bg_color: '#a78bfa'
    },
});

const { data: patients, error, status } = await useAsyncData(
    'diet-tags', 
    () => MealsService.getDietTags(diet_group.value, print_option.value, wards.value),
    {
        default: () => [],
    }
);
</script>

<template>
    <ViewTemplate :error="error" :status="status">
        <div class="grid grid-cols-3 w-[1071px]">
            <div v-for="patient in patients" class="px-6 py-1 w-[357px] h-[302px] border border-black break-inside-avoid break-after-auto">
                <div class="text-center text-xs">
                    <span>BGHMC NUTRITION AND DIETETICS DEPARTMENT</span>
                </div>

                <div class="border-b border-black text-center font-bold text-sm">
                    <span>FOOD SAFETY ASSURANCE TAG</span>

                    <p class="text-white" :style="{'background-color': meal_times?.[patient?.meal_time]?.bg_color ?? '#0f766e'}">
                        <span>{{ patient?.meal_time }}</span>
                        <span class="mx-4">|</span>
                        <span>{{ formatMonthShort(patient?.created_at) }}</span>
                    </p>
                </div>

                <div class="h-[220px] pt-2 font-bold text-xs">
                    <p class="text-center m-0">
                        <span>{{ patient?.wardname }}</span>
                        <span v-if="patient?.wardname === 'ER Boarders'"> | {{ patient?.wardname_o }}</span>
                    </p>

                    <Divider type="dashed" class="my-1" />

                    <p class="mb-2">
                        <span class="text-sm underline">{{ patient?.patname }}</span>
                    </p>

                    <p class="flex text-center items-top m-0">
                        <i class="pi pi-caret-right mr-2" style="font-size: 1rem"></i>
                        <span>{{ diet_group === 'ONS' ? 'SNS' : 'Diet' }}</span>
                        <span class="mx-2">:</span>
                        <span>{{ patient?.dietname }}</span>
                        <span v-if="patient?.dietname2" class="mx-1">+</span>
                        <span>{{ patient?.dietname2 }}</span>
                    </p>
                    
                    <div>
                        <p v-if="diet_group !== 'Oral' && patient?.dietname !== 'Blenderized'" class="flex text-center items-center m-0">
                            <i class="pi pi-caret-right mr-2" style="font-size: 1rem"></i>
                            <span>Brand</span>
                            <span class="mx-2">:</span>
                        </p>

                        <p v-if="diet_group === 'Oral' || (diet_group === 'Enteral' && patient?.dietname === 'Blenderized')" class="flex text-center items-center m-0">
                            <i class="pi pi-caret-right mr-2" style="font-size: 1rem"></i>
                            <span>Food Allergies</span>
                            <span class="mx-2">:</span>
                            <span>{{ patient?.food_allergies }}</span>
                        </p>
        
                        <p v-if="diet_group === 'Oral' || (diet_group === 'Enteral' && patient?.dietname === 'Blenderized')" class="flex text-center items-center m-0">
                            <i class="pi pi-caret-right mr-2" style="font-size: 1rem"></i>
                            <span>Precautions</span>
                            <span class="mx-2">:</span>
                            <span>{{ patient?.precaution }}</span>
                        </p>
                    </div>

                    <div class="text-xs">
                        <div class="w-full my-4 grid grid-cols-4">
                            <div class="col-span-1 text-center">
                                <p class="underline">Calories</p>
                                <p>&nbsp</p>
                            </div>

                            <div class="col-span-1 text-center">
                                <p class="underline">Protein</p>
                                <p>&nbsp</p>
                            </div>

                            <template v-if="diet_group !== 'Oral'">
                                <div class="col-span-1 text-center">
                                    <p class="underline">Volume</p>
                                    <p>&nbsp</p>
                                </div>
        
                                <div class="col-span-1 text-center">
                                    <p class="underline">Dilution</p>
                                    <p>&nbsp</p>
                                </div>
                            </template>
                            <template v-else>
                                <div class="col-span-1 text-center">
                                    <p class="underline">Fats</p>
                                    <p>&nbsp</p>
                                </div>
        
                                <div class="col-span-1 text-center">
                                    <p class="underline">Carbs</p>
                                    <p>&nbsp</p>
                                </div>
                            </template>
                        </div>

                        <div v-if="diet_group === 'ONS'">
                            <p>Please add water : </p>
                        </div>

                        <div>
                            <p>{{diet_group !== 'Oral' ? 'Time of Feeding' : 'Menu'}} : </p>
                        </div>
                    </div>
                </div>

                <div class="pt-1 border-t border-black text-center font-bold text-xs">
                    <span>Please consume this meal before </span>
                    <span v-if="diet_group === 'Oral'">{{ meal_times?.[patient?.meal_time]?.before_time }}</span>
                    <span v-else class="pr-20">:</span>
                </div>
            </div>
        </div>
    </ViewTemplate>
</template>