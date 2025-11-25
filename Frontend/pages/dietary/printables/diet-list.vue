<script setup>
import { MealsService } from '~/services/MealsService';
import { FoodService } from '~/services/FoodService';

definePageMeta({
    layout: 'printables'
});

const { user } = useUserStore();
const { setMealTimeSuffix } = useEnteralFeeding();
const route = useRoute();

const date = ref(route.query.date);
const diet_group = ref(route.query.group);
const meal_time = ref(route.query.mealtime);
const ward = ref(route.query.ward);

const miscs = ['Service', 'Payward', 'PHIC'];
const total_misc = ref({});

const { data: patients, error: patients_error, status: patients_status } = await useAsyncData(
    'diet-list-printable', 
    () => MealsService.getDietListPrintable(date.value, meal_time.value, ward.value),
    {
        default: () => [],
    }
);

const { data: service, error: service_error, status: service_status } = await useAsyncData(
    'served-report', 
    () => FoodService.getReport(date.value, ward.value, meal_time.value),
    {
        default: () => [],
    }
);


//  Categorizes patients into groups based on misc type
function groupMiscType({phic, diet_type_misc}){
    return phic === 'Y' ? 'PHIC' : diet_type_misc;
}


//  Watcher for data
watch(
    patients, 
    () => {
        total_misc.value = patients.value?.length ? Object.groupBy(patients.value, groupMiscType) : {};
    }, 
    {immediate: true}
);
</script>

<template>
    <ViewTemplate :error="patients_error" :status="patients_status">
        <table :class="{'w-[1070px]': diet_group !== 'Enteral', 'w-[1514px]': diet_group === 'Enteral'}">
            <thead class="h-56">
                <tr>
                    <td rowspan="3" class="w-48 border border-black">
                        <div class="flex items-center justify-center">
                            <img src="/images/bghmc-logo.png" alt="Icon" class="w-32 h-32" />
                        </div>
                    </td>
                    <td colspan="12" class="border border-black">
                        <div class="text-xs flex flex-col justify-center items-center">
                            <span>Republic of the Philippines</span>
                            <span>Department of Health</span>
                            <span class="text-sm font-bold">BAGUIO GENERAL HOSPITAL AND MEDICAL CENTER</span>
                            <span>Baguio City</span>
                        </div>
                    </td>
                </tr>
                <tr class="h-4">
                    <td colspan="12" class="border border-black">
                        <div class="flex text-xs">
                            <div class="flex-1 flex flex-col justify-center items-center">
                                <span class="font-semibold">NUTRITION AND DIETETICS DEPARTMENT</span>
                                <span class="text-xl font-bold">DIET LIST</span>
                            </div>
                            
                            <div>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="border border-t-0 border-black px-2">Form no.</td>
                                            <td class="border border-t-0 border-r-0 border-black px-2">MS-NDO-001</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-black px-2">Revision No.</td>
                                            <td class="border border-r-0 border-black px-2">2</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-b-0 border-black px-2">Effectivity Date</td>
                                            <td class="border border-b-0 border-r-0 border-black px-2">SEPTEMBER 1, 2016</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="h-8">
                    <td colspan="12" class="border border-black">
                        <div class="grid grid-cols-4 justify-items-center items-center font-bold">
                            <div class="col-span-1">
                                <span>{{ date }}</span>
                            </div>
                            <div class="col-span-2">
                                <span>{{ ward }}</span>
                            </div>
                            <div class="col-span-1">
                                <span>{{ meal_time }}{{ setMealTimeSuffix(meal_time) }}</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="h-10">
                    <th class="w-48 border border-black text-base">
                        <table>
                            <tbody>
                                <tr>
                                    <td class="w-32 text-left pl-2">Room</td>
                                    <td class="w-16 text-right pr-2">Bed</td>
                                </tr>
                            </tbody>
                        </table>
                    </th>
                    
                    <th class="border border-black text-base w-[218px]" :class="{'w-[300px]': diet_group === 'Enteral'}">Name of Patients</th>
                    <th class="border border-black text-base w-[70px]">Age</th>
                    <th class="border border-black text-base w-[70px]">Height</th>
                    <th class="border border-black text-base w-[70px]">Weight</th>
                    <th class="border border-black text-base w-[150px]">Diet Required</th>
                    
                    <template v-if="diet_group === 'Enteral'">
                        <th class="border border-black text-base w-[100px]">Calories</th>
                        <th class="border border-black text-base w-[100px]">Protein</th>
                        <th class="border border-black text-base w-[70px]">Freq</th>
                        <th class="border border-black text-base w-[70px]">Dil</th>
                    </template>
    
                    <th colspan="2" class="border border-black text-base">Remarks</th>
                </tr>
            </thead>
    
            <tbody>
                <tr v-for="patient in patients" class="break-inside-avoid break-after-auto">
                    <td class="border border-black text-sm text-left p-2">
                        <div>
                            <span v-if="patient.wardcode === 'ERB'">{{ patient.wardname_o }} - </span>
                            <span>{{ patient.rmname }}</span>
                        </div>
                        <div class="grid grid-cols-2 italic">
                            <div class="col-span-1 text-xs">
                                <span>{{ patient.phic === 'Y' ? 'PHIC' : patient.diet_type_misc }}</span>
                            </div>
                            <div class="col-span-1 text-right">
                                <span>{{ patient.bdname }}</span>
                            </div>
                        </div>
                    </td>
                    
                    <td class="border border-black p-2">
                        <span class="text-xl">{{ patient.patname }}</span>
                    </td>
    
                    <td class="border border-black text-center p-2">
                        <span class="text-base">{{ patient.age }} {{ patient.agedesc }}</span>
                    </td>
    
                    <td class="border border-black text-center p-2">
                        <span class="text-base">{{ patient.height }}</span>
                    </td>
    
                    <td class="border border-black text-center p-2">
                        <span class="text-base">{{ patient.weight }}</span>
                    </td>
    
                    <td class="border border-black text-center p-2">
                        <span class="text-xl">{{ patient.dietname }}</span>
                        <span v-if="patient.dietname2" class="m-1">+</span>
                        <span class="text-xl">{{ patient.dietname2 }}</span>
                    </td>
                    
                    <template v-if="diet_group === 'Enteral'">
                        <td class="border border-black text-center p-2">
                            <span class="text-base">{{ patient.calories }}</span>
                        </td>
                        <td class="border border-black text-center p-2">
                            <span class="text-base">{{ patient.protein }}</span>
                        </td>
                        <td class="border border-black text-center p-2">
                            <span class="text-base">{{ patient.feedingFrequency }}</span>
                        </td>
                        <td class="border border-black text-center p-2">
                            <span class="text-base">{{ patient.dilution }}</span>
                        </td>
                    </template>
    
                    <td colspan="2" class="border border-black text-base py-2">
                        <div class="flex-col items-start text-start px-6">
                            <ul class="list-disc">
                                <li v-if="patient.food_allergies && patient.food_allergies !== 'No Known Allergy'">
                                    <span>Allergies: {{ patient.food_allergies }}</span> 
                                </li>
                                <li v-if="patient.precaution">
                                    <span>{{ patient.precaution }}</span>
                                </li>
                                <li v-if="patient.ordreas">
                                    <span>{{ patient.ordreas }}</span>
                                </li>
                                <li v-if="patient.onsDescription">
                                    <span>{{ patient.onsDescription }}</span>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
    
                <tr class="break-inside-avoid break-after-auto">
                    <td colspan="12" class="pb-8">
                        <div class="grid grid-cols-12 my-2">
                            <div class="col-span-3 grid grid-cols-2 justify-items-start">
                                <div class="col-span-1">
                                    <span class="font-semibold">Total Meals: </span>
                                </div>
                                <div class="col-span-1">
                                    <span class="font-medium">{{ patients.length }}</span>
                                </div>
                            </div>
                            <div class="col-span-5">
                                <div v-for="m in miscs" class="grid grid-cols-3 justify-items-start">
                                    <div class="col-span-1">
                                        <span class="font-semibold">Total {{ m }}: </span>
                                    </div>
                                    <div class="col-span-2 font-medium">
                                        <span>{{ total_misc?.[m]?.length ?? 0 }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-span-4 grid grid-cols-3">
                                <div class="col-span-1 w-full flex justify-end">
                                    <span class="font-semibold">Printed By: </span>
                                </div>
                                <div class="col-span-2 w-full pt-10 flex justify-start">
                                    <div class="flex flex-col text-center">
                                        <span class="font-semibold uppercase ml-2">{{ `${user?.firstname.toUpperCase()} ${user?.middlename} ${user?.lastname}`.toUpperCase() }}</span>
                                        <span class="font-semibold overline">Signature Over Printed Name</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
    
                <tr class="break-inside-avoid break-after-auto border-t border-black border-dashed">
                    <td colspan="12" class="pt-8">
                        <table class="text-left break-inside-avoid break-after-auto" :class="{'w-[1070px]': diet_group !== 'Enteral', 'w-[1514px]': diet_group === 'Enteral'}">
                            <tbody>
                                <tr>
                                    <th colspan="3" class="text-center">FOOD SERVICE MONITORING REPORT</th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="px-2">Date: {{ date }}</th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="px-2">Ward: {{ ward }}</th>
                                </tr>
                                <tr>
                                    <th colspan="3" class="px-2">Assigned Food Server: {{ service?.[0]?.server ?? '' }}</th>
                                </tr>
                                <tr>
                                    <th class="border border-black px-2 w-[25%]"></th>
                                    <th class="border border-black px-2 text-center w-[25%]">Verification of Nurse on Duty</th>
                                    <th class="border border-black px-2 text-center w-[50%]">Remarks</th>
                                </tr>
                                <tr>
                                    <th class="border border-black px-2">No. of patients in the ward</th>
                                    <td class="border border-black px-2 text-center">{{ service?.[0]?.total ?? '' }}</td>
                                    <td rowspan="4" class="border border-black px-2">{{ service?.[0]?.remarks ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th class="border border-black px-2">No. of patients served</th>
                                    <td class="border border-black px-2 text-center">{{ service?.[0]?.served ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th class="border border-black px-2">No. of trays not served</th>
                                    <td class="border border-black px-2 text-center">{{ service?.[0]?.not_served ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th class="border border-black px-2">No. of patients not given food</th>
                                    <td class="border border-black px-2 text-center">{{ service?.[0]?.not_given ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="border border-black px-2 h-20">
                                        <p class="flex flex-col items-start h-full">
                                            <span>Names of Unlisted Patients:&nbsp</span>
                                            <span>{{ service?.[0]?.unlisted_patients ?? '' }}</span>
                                        </p>
                                    </th>
                                </tr>
                                <tr>
                                    <td colspan="3" class="px-2">
                                        <div class="grid grid-cols-2 my-2">
                                            <div class="grid col-span-1 grid-cols-3">
                                                <div class="col-span-1 w-full flex justify-end">
                                                    <span class="font-semibold">Submitted By: </span>
                                                </div>
                                                <div class="col-span-2 w-full pt-10 flex justify-start">
                                                    <div class="flex flex-col text-center">
                                                        <span class="font-semibold uppercase ml-2">{{ service?.[0]?.server.toUpperCase() ?? '' }}</span>
                                                        <span class="font-semibold overline">Name and Signature of Food Server</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="grid col-span-1 grid-cols-3">
                                                <div class="col-span-1 w-full flex justify-end">
                                                    <span class="font-semibold">Verified By: </span>
                                                </div>
                                                <div class="col-span-2 w-full flex justify-start" :class="{'pt-10': !service?.[0]?.nurse_signature}">
                                                    <div class="flex flex-col text-center">
                                                        <img :src="service?.[0]?.nurse_signature">
                                                        <span class="font-semibold uppercase ml-2">{{ service?.[0]?.nurse.toUpperCase() ?? '' }}</span>
                                                        <span class="font-semibold overline">Name and Signature of Nurse on Duty</span>
                                                        <span v-if="service?.[0]?.nurse_signature">(Electronically signed)</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>

            <tfoot class="hidden print:table-footer-group">
                <tr>
                    <td colspan="13">
                        <ClientOnly>
                            <p class="text-right text-sm italic">Printed {{ new Date() }}</p>
                        </ClientOnly>
                    </td>
                </tr>
            </tfoot>
        </table>
    </ViewTemplate>
</template>