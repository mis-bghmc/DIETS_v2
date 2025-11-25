<script setup>
import { MealsService } from '~/services/MealsService';
import NoData from '~/public/images/no-data.svg';

const echo = useEcho();
const { getPrimary, getSurface, isDarkTheme } = useLayout();
const { setMealTimeSuffix } = useEnteralFeeding();
const { formatAllNumeric, formatMonthShort } = useDate();

const props = defineProps({
    date: Date
});

const diet_groups = ref({
    Oral: {
        BREAKFAST: [],
        LUNCH: [],
        DINNER: []
    }, 
    Enteral: {

    }, 
    SNS: {
        AM: [],
        PM: [],
        MN: []
    }
});
const diet_group = ref('Oral');
const meal_time = ref('BREAKFAST');

const types = ref(['Wards', 'Diets']);
const type = ref('Wards');

const census = ref([]);

const updated = ref();

const expand = ref(false);

const chartData = ref(null);
const chartOptions = ref(null);

const { data, error, status } = await useAsyncData(
    'meal-census', 
    () => MealsService.getMealCensus(formatAllNumeric(props.date)),
    {
        default: () => []
    }
);


//  Categorizes patients into groups based on diet
function getDietGroup({diettype, meal_time}) {
    return ['AM', 'PM', 'MN'].includes(meal_time) ? 'SNS' : diettype === 'EN' ? 'E' : 'O';
}

//  Update census for changes
function filterMealCensus() {
    const _census = type.value === 'Wards' ? setWards() : setDiets();
    census.value = useSort().sortArray(_census, 'name');
    
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
}

//  Set meal times
function setMealTimes(){
    meal_time.value = Object.keys(diet_groups.value?.[diet_group.value])?.[0];
}

//  Set wards
function setWards() {
    const ward_grouped_patients = Object.groupBy(diet_groups.value?.[diet_group.value]?.[meal_time.value] ?? {}, ({wardname}) => wardname);
    
    return Object.keys(ward_grouped_patients)?.map((key) => ({ 
        name: key, 
        total: ward_grouped_patients[key]?.length ?? 0,
        precautions: Object.groupBy(ward_grouped_patients[key], checkDietPrecautions)
    })) ?? {};
}

//  Categorizes data into diets with and without precautions
function checkDietPrecautions({dietname, precaution, food_allergies}) {
    return precaution || (food_allergies && food_allergies !== 'No Known Allergy') 
    ? `${dietname} [ ${[precaution, food_allergies].filter(Boolean).join(' + ')} ]` 
    : dietname;
}

//  Set Diets
function setDiets() {
    const group_by = diet_group.value !== 'SNS' ? ({dietname}) => dietname?.trim() : checkAdditionalOns;

    const diet_grouped_patients = Object.groupBy(diet_groups.value?.[diet_group.value]?.[meal_time.value] ?? {}, group_by);
    
    return Object.keys(diet_grouped_patients)?.map((key) => ({
        name: key, 
        total: diet_grouped_patients[key]?.length ?? 0,
        precautions: Object.groupBy(diet_grouped_patients[key], checkPrecautions)
    })) ?? {};
}

//  Categorizes data into diets with and without additional SNS
function checkAdditionalOns({dietname, dietname2}) {
    return `${[dietname, dietname2].filter(Boolean).join(' + ').trim()}`
}

//  Categorizes precautions and food allergies
function checkPrecautions({precaution, food_allergies}) {
    return (food_allergies && food_allergies !== 'No Known Allergy') 
    ? `${[precaution, food_allergies].filter(Boolean).join(' + ')}` 
    : precaution || 'No Precautions & Allergies';
}

//  Set chart data
function setChartData() {
    if(import.meta.server) return;

    const documentStyle = getComputedStyle(document.documentElement);
    
    return {
        labels: Object.values(census.value)?.map(item => item.name),
        datasets: [
            {
                type: 'bar',
                label: 'Meals',
                backgroundColor: documentStyle.getPropertyValue('--p-primary-500'),
                data: Object.values(census.value)?.map(item => item.total),
                barThickness: 10
            }
        ]
    };
}

//  Set chart options
function setChartOptions() {
    if(import.meta.server) return;

    const documentStyle = getComputedStyle(document.documentElement);
    const borderColor = documentStyle.getPropertyValue('--surface-border');
    const textMutedColor = documentStyle.getPropertyValue('--text-color-secondary');

    return {
        indexAxis: 'y',
        maintainAspectRatio: true,
        aspectRatio: 0.8,
        scales: {
            x: {
                position: 'top',
                stacked: true,
                ticks: {
                    color: textMutedColor
                },
                grid: {
                    color: 'transparent',
                    borderColor: 'transparent'
                }
            },
            y: {
                stacked: true,
                ticks: {
                    color: textMutedColor
                },
                grid: {
                    color: borderColor,
                    borderColor: 'transparent',
                    drawTicks: false
                }
            }
        }
    };
}


//  Meal census
watch(
    data, 
    (new_value) => {
    const grouped_patients = Object.groupBy(new_value ?? [], getDietGroup);

    const oral = Object.groupBy(grouped_patients?.O ?? [], ({meal_time}) => meal_time);
    const enteral = Object.groupBy(grouped_patients?.E ?? [], ({meal_time}) => meal_time);
    const sns = Object.groupBy(grouped_patients?.SNS ?? [], ({meal_time}) => meal_time);

    diet_groups.value = {
        Oral: { 
            BREAKFAST: oral?.BREAKFAST ?? [],
            LUNCH: oral?.LUNCH ?? [],
            DINNER: oral?.DINNER ?? []
        },
        Enteral: enteral,
        SNS: { 
            AM: sns?.AM ?? [],
            PM: sns?.PM ?? [],
            MN: sns?.MN ?? []
        },
    };

    filterMealCensus();
    
    }, 
    { immediate: true }
);

//  Theme
watch([getPrimary, getSurface, isDarkTheme], () => {
    chartData.value = setChartData();
    chartOptions.value = setChartOptions();
});

//  Watcher for updates
watch(updated, (new_value) => {
    census.value.forEach((c, outer) => {
        Object.entries(c.precautions).forEach(([inner, precaution]) => {
            precaution.forEach((p, i) => {
                if(p.id === new_value.message.id) {
                    census.value[outer].precautions[inner][i].meal_status = new_value.message.meal_status;
                }
            });
        });
    });
});


//  On mounted
onMounted(()=> {
    echo.channel('status-update-channel')    
        .listen('.status-updated', (e) => {
            updated.value = e;
        })
        .listen('.ons-status-updated', (e) => {
            updated.value = e;
        })
});
</script>

<template>
    
    <div class="card">
        <ViewTemplate :error="error" :status="status">
            <div class="flex justify-between gap-2 items-center">
                <div class="flex flex-1 gap-1 items-center">
                    <Button text :icon="expand ? 'pi pi-minus' : 'pi pi-plus'" :disabled="!census?.length" @click="expand = !expand" />
                    <span class="font-semibold text-xl text-muted-color">Meal Census - {{ meal_time }}{{ setMealTimeSuffix(meal_time) }}</span>
                    <MealCensus :date="formatMonthShort(date)" :meal-time="meal_time" :census="census" :type="type"/>
                </div>
    
                <div class="relative">
                    <Button 
                        text 
                        icon="pi pi-cog" 
                        v-styleclass="{ selector: '@next', enterFromClass: 'hidden', enterActiveClass: 'animate-scalein', leaveToClass: 'hidden', leaveActiveClass: 'animate-fadeout', hideOnOutsideClick: true }"
                    />
                    
                    <div class="absolute hidden right-0 mt-1 w-64 border-2 border-primary p-4 rounded h-auto z-50 sm:text-sm md:text-base lg:text-lg bg-[--surface-overlay]">
                        <div class="flex flex-col gap-2">
                            <FloatLabel variant="on">
                                <Select id="group" v-model="diet_group" :options="Object.keys(diet_groups)" class="!w-full" @change="setMealTimes(), filterMealCensus()" />
                                <label for="group">Group</label>
                            </FloatLabel>
    
                            <FloatLabel variant="on">
                                <Select id="meal_time" v-model="meal_time" :options="Object.keys(diet_groups?.[diet_group])" class="!w-full" @change="filterMealCensus()" />
                                <label for="meal_time">Meal Time</label>
                            </FloatLabel>
                            
                            <FloatLabel variant="on">
                                <Select id="type" v-model="type" :options="types" class="!w-full" @change="filterMealCensus()" />
                                <label for="type">Type</label>
                            </FloatLabel>
                        </div>
                    </div>
                </div>
            </div>
    
            <div :class="['h-[25vh] overflow-hidden', {'h-max overflow-visible': expand}]" @click="expand = true">
                <div v-if="!census?.length" class="container mx-auto flex flex-col justify-center items-center py-4 h-[19vh]">
                    <NoData class="text-primary" />
                    <p class="text-lg font-bold mt-4">No data found.</p>
                </div>
                <div v-else>
                    <Chart type="bar" :data="chartData" :options="chartOptions"/>
                </div>
            </div>
        </ViewTemplate>
    </div>
</template>