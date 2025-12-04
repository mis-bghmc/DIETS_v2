<script setup>
import { PatientsService } from '~/services/PatientsService';
import NoData from '~/public/images/no-data.svg';

const props = defineProps({
    enccode: String
});

const { patient } = useRoute().params;
const enccode = decodeURIComponent(patient || '');

const { data, error, status } = await useAsyncData(
    `patient-data-${patient}`, 
    () => PatientsService.getPatientData(decodeURIComponent(props.enccode)),
    {
        default: () => []
    }
);

const diet_details = ref();
const diet_history = ref();


//  Update diet details
function updateDietDetails(event) {
    diet_details.value = event.find(p => p.dostatus === 'A' && p.enccode === props.enccode);
}

//  Refresh diet history
function refreshHistory() {
    nextTick(async () => {
        await diet_history.value?.refresh();
    });
}

//  Update food allergies
function updateFoodAllergies(food_allergies) {
    data.value[0].category = food_allergies;
}

//  Update precuations
function updatePrecautions(precautions) {
    data.value[0].precaution = precautions;
}
</script>

<template>
    <WithSuspense>
        <ViewTemplate :error="error" :status="status">
            <div v-if="!Object.keys(data)?.length" class="container mx-auto flex flex-col justify-center items-center py-4 h-[75vh]">
                <NoData class="text-primary" />
                <p class="text-lg font-bold mt-4">No data found.</p>
            </div>
    
            <div v-else class="container mx-auto flex flex-col gap-6">
                <div class="bg-[--surface-card] p-6 rounded-md grid grid-cols-2 gap-4 items-center">
                    <div class="col-span-2 md:col-span-1 text-left">
                        <h4 class="m-0">Patient Diet Information</h4>
                        <p class="text-xs">Comprehensive Dietary and Nutrition Profile</p>
                    </div>
                    
                    <div class="col-span-2 md:col-span-1 flex justify-end">
                        <DoctorsOrderForm :data="data" @updated="refreshHistory" class="w-full md:w-auto" />
                    </div>
                </div>
        
                <div class="grid grid-cols-6 gap-6 h-auto">
                    <div class="col-span-6 md:col-span-3 lg:col-span-2 flex flex-col gap-6">
                        <div class="bg-[--surface-card] p-6 rounded-md h-full">
                            <AdmissionDetails :data="data" />
                        </div>
        
                        <div class="bg-[--surface-card] p-6 rounded-md h-auto">
                            <NutritionDetails :data="data" />
                        </div>
                    </div>
        
                    <div class="col-span-6 md:col-span-3 lg:col-span-4">
                        <div class="bg-[--surface-card] p-6 rounded-md h-full">
                            <DietDetails :data="diet_details" :from-history="false" @allergy-updated="updateFoodAllergies" @precautions-updated="updatePrecautions" />
                        </div>
                    </div>
                </div>
        
                <div class="bg-[--surface-card] p-6 rounded-md h-max">
                    <DietHistory ref="diet_history" :data="data" @updated="updateDietDetails" />
                </div>
            </div>
        </ViewTemplate>
    </WithSuspense>
</template>