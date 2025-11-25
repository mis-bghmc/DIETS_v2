<script setup>
import { NutritionService } from '~/services/NutritionService';
import data_nutrition from '~/assets/data/nutrition.json';
import NoData from '~/public/images/no-data.svg';

const { formatDateTime } = useDate();

const props = defineProps({
    data: Object,
    height: String,
    weight: String,
    bmi: Number,
    ageBracket: String
});

const emit = defineEmits(['updated']);

const questions = data_nutrition.questions[props.ageBracket];
const patient = ref(props.data?.[0]);

const { data : history, error, status, refresh } = await useAsyncData(
    'nutrition-screening-history', 
    () => NutritionService.getScreeningHistory(patient.value?.enccode),
    {
        default: () => []
    }
);


//  Update screening
function update() {
    nextTick(async () => {
        await refresh();
        emit('updated');
    });
}
</script>

<template>
    <ViewTemplate :error="error" :status="status">
        <div class="flex flex-col gap-4 text-base">
            <div class="w-full border border-primary border-dashed py-4 px-4 rounded-sm">
                <p class="font-bold">Nutrition Screening Questions:</p>
                <ol class="list-decimal pl-10">
                    <li v-for="q in questions">{{ q }}</li>
                </ol>
            </div>

            <NutritionScreeningForm 
                :enccode="patient?.enccode" 
                :height="height"
                :weight="weight"
                :bmi="bmi"
                :questions="questions" 
                class="flex justify-end w-full" 
                @updated="update()"
            />
        
            <DataTable 
                :value="history" 
                stripedRows 
                scrollable
                scrollHeight="50vh"
                :rowHover="true" 
            >
                <template #empty>
                    <div class="container mx-auto flex flex-col justify-center items-center py-4 h-[40vh]">
                        <NoData class="text-primary" />
                        <p class="text-lg font-bold mt-4">No data found.</p>
                    </div>
                </template>

                <Column field="datepost" header="Date" sortable frozen class="text-nowrap">
                    <template #body="{data, field}">
                        <span>{{ formatDateTime(data[field]) }}</span>
                    </template>
                </Column>  

                <Column field="height" headerClass="text-center">
                    <template #header>
                        <div class="flex justify-center w-full">
                            <span class="font-bold">Height</span>
                        </div>
                    </template>

                    <template #body="{data,field}">
                        <div class="flex justify-center w-full">
                            <span>{{ data[field] }}</span>
                        </div>
                    </template>
                </Column>

                <Column field="weight" headerClass="text-center">
                    <template #header>
                        <div class="flex justify-center w-full">
                            <span class="font-bold">Weight</span>
                        </div>
                    </template>

                    <template #body="{data,field}">
                        <div class="flex justify-center w-full">
                            <span>{{ data[field] }}</span>
                        </div>
                    </template>
                </Column>

                <Column field="bmi" headerClass="text-center">
                    <template #header>
                        <div class="flex justify-center w-full">
                            <span class="font-bold">BMI</span>
                        </div>
                    </template>

                    <template #body="{data,field}">
                        <div class="flex justify-center w-full">
                            <span>{{ data[field] }}</span>
                        </div>
                    </template>
                </Column>

                <Column field="question1" headerClass="text-center">
                    <template #header>
                        <div class="flex justify-center w-full">
                            <span class="font-bold"># 1</span>
                        </div>
                    </template>

                    <template #body="{data,field}">
                        <div class="flex justify-center w-full">
                            <template v-if="data[field] === 'Y'">
                                <i class="pi pi-check text-emerald-400 !text-3xl"></i>
                            </template>
                            <template v-else>
                                <i class="pi pi-times text-red-400 !text-3xl"></i>
                            </template>
                        </div>
                    </template>
                </Column>

                <Column field="question2" headerClass="text-center">
                    <template #header>
                        <div class="flex justify-center w-full">
                            <span class="font-bold"># 2</span>
                        </div>
                    </template>

                    <template #body="{data,field}">
                        <div class="flex justify-center w-full">
                            <template v-if="data[field] === 'Y'">
                                <i class="pi pi-check text-emerald-400 !text-3xl"></i>
                            </template>
                            <template v-else>
                                <i class="pi pi-times text-red-400 !text-3xl"></i>
                            </template>
                        </div>
                    </template>
                </Column>

                <Column field="question3" headerClass="text-center">
                    <template #header>
                        <div class="flex justify-center w-full">
                            <span class="font-bold"># 3</span>
                        </div>
                    </template>

                    <template #body="{data,field}">
                        <div class="flex justify-center w-full">
                            <template v-if="data[field] === 'Y'">
                                <i class="pi pi-check text-emerald-400 !text-3xl"></i>
                            </template>
                            <template v-else>
                                <i class="pi pi-times text-red-400 !text-3xl"></i>
                            </template>
                        </div>
                    </template>
                </Column>

                <Column field="question4" headerClass="text-center">
                    <template #header>
                        <div class="flex justify-center w-full">
                            <span class="font-bold"># 4</span>
                        </div>
                    </template>

                    <template #body="{data,field}">
                        <div class="flex justify-center w-full">
                            <template v-if="data[field] === 'Y'">
                                <i class="pi pi-check text-emerald-400 !text-3xl"></i>
                            </template>
                            <template v-else>
                                <i class="pi pi-times text-red-400 !text-3xl"></i>
                            </template>
                        </div>
                    </template>
                </Column>

                <Column field="riskIndicator" headerClass="text-center">
                    <template #body="{data, field}">
                        <div class="w-full">
                            <Tag :severity="data[field] === 'Nutritionally at Risk' ? 'danger' : 'success'" class="w-full text-xl font-bold">{{ data[field] }}</Tag>
                        </div>
                    </template>
                </Column>
            </DataTable>
        </div>
    </ViewTemplate>
</template>