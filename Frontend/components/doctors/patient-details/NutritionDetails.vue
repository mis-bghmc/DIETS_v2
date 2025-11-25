<script setup>
import { PatientsService } from '~/services/PatientsService';

const props = defineProps({
    data: Object
});

const patient = props.data?.[0];

const { data: measurement, error, status, refresh } = await useAsyncData(
    `patient-measurements-${patient?.enccode}`, 
    () => PatientsService.getPatientMeasurements(patient?.enccode),
    {
        default: () => []
    }
);

const visible = ref(false);
const updated = ref(false);
const age_bracket = Number(patient?.patage) > 18 ? 'Adult' : 'Pedia'
const RiskIndicator = computed(() => { 

    if (!measurement.value?.riskIndicator) return {
        severity: 'info',
        color: 'blue',
        icon: 'fluent:info-12-filled',
        label: 'No Nutrition Screening found.'
    };

    if (measurement.value?.riskIndicator === 'Nutritionally at Risk') {
        return {
            severity: 'danger',
            color: 'red',
            icon: 'fluent:warning-24-filled',
            label: 'Nutritionally at Risk'
        }
    } else {
        return {
            severity: 'success',
            color: 'emerald',
            icon: 'fluent:thumb-like-28-filled',
            label: 'Not at Risk'
        }
    }
});




//  Compute BMI
function getBMI() {
    const height = measurement.value?.height;
    const weight = measurement.value?.weight;

    if(!height || !weight) return;

    const bmi = weight / Math.pow(height / 100, 2);

    return Math.round(bmi * 100) / 100;
}

//  Refetch measurement
function update() {
    if(updated.value){
        updated.value = false;
        refresh();
    }
}
</script>

<template>
    <ViewTemplate :error="error" :status="status">
        <div>
            <h5 class="flex gap-2 items-center"> 
                <Icon name="healthicons:weight" size="1.5em" class="text-primary"/> 
                <span class="flex-1 font-bold">Patient Nutrition Overview</span>
                <Button text class="p-0" @click="visible = true" v-tooltip.bottom="'Open Patient Nutrition'"> 
                    <Icon name="fluent:open-12-filled" size="2rem" class="icon" />
                </Button>
            </h5>
        </div>

        <div class="flex flex-col gap-6">
            <div class="flex justify-between gap-4 items-center">
                <div class="flex flex-col text-center">
                    <label class="text-muted-color italic text-sm">Height (cm)</label>
                    <span class="font-semibold">{{ measurement?.height ?? '-' }}</span>  
                </div>

                <div class="flex flex-col text-center">
                    <label class="text-muted-color italic text-sm">Weight (kg)</label>
                    <span class="font-semibold">{{ measurement?.weight ?? '-' }}</span>  
                </div>

                <div class="flex flex-col text-center">
                    <label class="text-muted-color italic text-sm">BMI</label>
                    <span class="font-semibold">{{ getBMI() ?? '-' }}</span>  
                </div>
            </div>

            <div>
                <Button variant="outlined" class="w-full text-lg font-bold" :severity="RiskIndicator.severity" @click="visible = true" v-tooltip.bottom="'Open Patient Nutrition'">
                    <Icon :name="RiskIndicator.icon" size="1.5em" :class="'text-' + RiskIndicator.color + '-200'"/> 
                    <span class="flex-1 font-bold" >{{ RiskIndicator.label }}</span>
                </Button>
            </div>
        </div>

        <Dialog 
            modal
            v-model:visible="visible" 
            :dismissableMask=false
            :draggable=false
            pt:root:class="!w-full md:!w-11/12 !h-full !border-primary !border-2 !rounded-md sm:!text-sm md:!text-base lg:!text-lg" 
            pt:header:class="!pb-2 !pt-3 !border-b !border-primary"
            pt:content:class="!p-0"
            pt:mask:class="!backdrop-blur-sm" 
            @after-hide="update()"
        >
            <template #header>
                <div class="flex my-5 gap-2 items-center">
                    <Icon name="healthicons:nutrition" size="2em" />
                    <span class="font-bold">Patient Nutrition Screening & Assessments</span>
                </div>
            </template>
            <div class="p-2">
                <Tabs value="0">
                    <TabList>
                        <Tab value="0">Screening</Tab>
                        <Tab value="1">Assessment</Tab>
                    </TabList>
                    <TabPanels>
                        <TabPanel value="0">
                            <NutritionScreenings 
                                :data="data" 
                                :height="measurement.height" 
                                :weight="measurement.weight" 
                                :bmi="getBMI()" 
                                :age-bracket="age_bracket" 
                                @updated="updated = true"
                            />
                        </TabPanel>
                        <TabPanel value="1">
                            <NutritionAssessments :data="data" />
                        </TabPanel>
                    </TabPanels>
                </Tabs>
            </div>
            
        </Dialog>
    </ViewTemplate>
</template>