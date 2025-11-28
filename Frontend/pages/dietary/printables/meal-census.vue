<script setup>
import { MealsService } from '~/services/MealsService';

definePageMeta({
    layout: 'printables'
});

const { setMealTimeSuffix } = useEnteralFeeding();
const route = useRoute();

const date = ref(route.query.date);
const meal_time = ref(route.query.mealtime);
const diet_group = ref(route.query.group);

const { data, error, status } = await useAsyncData(
    'meal-census-printable', 
    () => MealsService.getMealCensus(date.value),
    {
        default: () => []
    }
);

const filtered = ref(data.value?.filter(census => census.meal_time === meal_time.value));

const ward_grouped = ref({});
const diet_grouped = ref({});

const wards = ref([]);
const diets = ref([]);

const room_types = ref(['PHIC', 'Payward', 'Service']);

const ward_groups = ref({});
const wards_checkbox = ref([]);

const diets_selected = ref({
    Oral: {
        'Breast Feeding': true,
        'Full': true,
        'Soft': true,
        'Therapeutic': true,
        'Liquid': true,
        'NPO': true
    },
    Enteral: {
        'Blenderized': true,
        'Cancer Specific': true,
        'Diabetic': true,
        'Renal Specific': true,
        'Semi-elemental': true,
        'Standard Polymeric': true,
        'Therap Milk': true
    },
    SNS: {
        'Cancer Specific': true,
        'Diabetic': true,
        'Renal Specific': true,
        'Semi-elemental': true,
        'Standard Polymeric': true,
        'Therap Milk': true
    }
});

const miscs = ref({
    room_type: true,
    precautions_allergies: false
});


//  Set wards
function setWards() {
    const group_by = Object.keys(ward_groups.value)?.length ? checkGroups : ({wardname}) => wardname;

    ward_grouped.value = Object.groupBy(filtered.value, group_by);
    wards.value = Object.keys(ward_grouped.value || []).sort();
    wards_checkbox.value = [...wards.value];

    checkWards();
}

//  List wards by ward/group name
function checkGroups({wardname}) {
    for (let [group, data] of Object.entries(ward_groups.value)) {
        if (data.wards?.includes(wardname)) {
            return data.groupname;
        }
    }
    
    return wardname;
}

//  Filter out wards that are already part of a group or are themselves a group
function checkWards() {
    wards_checkbox.value = wards_checkbox.value?.filter(
        (w) => !Object.values(ward_groups.value)?.some((group) => group.wards?.includes(w) || group.groupname === w)
    );
}

//  Set diets
function setDiets() {
    const group_by = miscs.value['precautions_allergies'] ? checkAllergiesPrecautions : ({dietname}) => dietname;
    const selected_diets = Object.keys(diets_selected.value?.[diet_group.value]).filter(key => diets_selected.value?.[diet_group.value]?.[key]);
    const diets_filtered = miscs.value['precautions_allergies'] 
        ? filtered.value?.filter(diet => selected_diets?.some(str => diet.dietname?.includes(str)) || (diet.diettype === 'TP' && selected_diets.includes('Therapeutic'))) 
        : filtered.value;
    
    diet_grouped.value = Object.groupBy(diets_filtered, group_by);
    diets.value = Object.keys(diet_grouped.value || []).sort();

    if(diet_group.value === 'Enteral' && diets.value?.length) diets.value.push('FTW');
}

//  List diets with their allergies and precautions if any
function checkAllergiesPrecautions({dietname, precaution, food_allergies}) {
    if(dietname?.includes('NPO')) return dietname;
    
    const precautions_allergies = (food_allergies && food_allergies !== 'No Known Allergy') 
        ? [precaution, food_allergies].filter(Boolean).join(' | ')
        : precaution || null;
    
    return precautions_allergies ? `${dietname} (${precautions_allergies})` : dietname;
}

//  Get diet count per ward
function getWardCount(ward, diet) {
    const dietname = (item) => {
        return miscs.value['precautions_allergies'] 
            ? checkAllergiesPrecautions({dietname: item.dietname, precaution: item.precaution, food_allergies: item.food_allergies}) 
            : item.dietname;
    }

    const filter = (item) => {
        return diet_group.value === 'Enteral' && diet === 'FTW' ? diets.value?.includes(dietname(item)) : dietname(item) === diet;
    } 
    
    return ward_grouped.value?.[ward]?.filter(filter)?.length;
}

//  Get diet total per ward
function getWardTotal(diet) {
    if(diet_group.value === 'Enteral' && diet === 'FTW') {
        return filtered.value?.filter(item => diets.value?.some(d => d.includes(item.dietname))).length;
    }

    return diet_grouped.value?.[diet]?.length;
}

//  Get room type of each ward
function getWardRoomTypes(ward) {
    const room_type = Object.groupBy(ward_grouped.value?.[ward], checkRoomType);
    
    return Object.keys(room_type)?.sort();
}

//  Categorizes patients into groups based on room type
function checkRoomType({phic, type_desc}) {
    return phic === 'Y' ? 'PHIC' : type_desc;
}

//  Get room type total per ward
function getWardRoomTypeTotal(ward, room_type) {
    return Object.groupBy(ward_grouped.value?.[ward], checkRoomType)?.[room_type]?.length;
}

//  Get diet count per room type, ward
function getWardRoomTypeCount(ward, room_type, diet) {
    const dietname = (item) => {
        return miscs.value['precautions_allergies'] 
            ? checkAllergiesPrecautions({dietname: item.dietname, precaution: item.precaution, food_allergies: item.food_allergies}) 
            : item.dietname;
    }

    const filter = (item) => {
        return diet_group.value === 'Enteral' && diet === 'FTW' ? diets.value?.includes(dietname(item)) : dietname(item) === diet;
    } 

    return Object.groupBy(ward_grouped.value?.[ward], checkRoomType)?.[room_type]?.filter(filter)?.length;
}

//  Get count per room type
function getRoomTypeTotal(room_type) {
    return Object.groupBy(filtered.value, checkRoomType)?.[room_type]?.length;
}

//  Get room type count per diet
function getDietRoomTypeCount(room_type, diet) {
    if(diet_group.value === 'Enteral' && diet === 'FTW') {
        return filtered.value?.filter(item => diets.value?.some(d => d.includes(item.dietname)) && checkRoomType(item) === room_type).length;
    }

    return diet_grouped.value?.[diet]?.filter(item => checkRoomType(item) === room_type)?.length;
}


// Watcher for 
watch(data, () => { setWards(); setDiets() }, { immediate: true });
</script>

<template>
    <ViewTemplate :error="error" :status="status">
        <table class="w-[1514px]">
            <thead>
                <tr class="h-auto">
                    <td rowspan="3" class="w-48 border border-black">
                        <div class="flex justify-center items-center">
                            <img src="/images/bghmc-logo.png" alt="Icon" class="w-32 h-32" />
                        </div>
                    </td>
                    <td :colspan="diets?.length + 1" class="border border-black">
                        <div class="text-xs flex flex-col justify-center items-center">
                            <span>Republic of the Philippines</span>
                            <span>Department of Health</span>
                            <span class="text-sm font-bold">BAGUIO GENERAL HOSPITAL AND MEDICAL CENTER</span>
                            <span>Baguio City</span>
                        </div>
                    </td>
                </tr>
                <tr class="h-4">
                    <td :colspan="diets?.length + 1" class="border border-black">
                        <div class="flex text-xs">
                            <div class="flex-1 flex flex-col justify-center items-center">
                                <span class="font-semibold">NUTRITION AND DIETETICS DEPARTMENT</span>
                                <span class="text-xl font-bold">MEAL CENSUS</span>
                            </div>
                            
                            <div>
                                <table>
                                    <tbody>
                                        <tr>
                                            <td class="border border-t-0 border-black px-2">Form no.</td>
                                            <td class="border border-t-0 border-r-0 border-black px-2">AHPS-NDD-20</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-black px-2">Revision No.</td>
                                            <td class="border border-r-0 border-black px-2">0</td>
                                        </tr>
                                        <tr>
                                            <td class="border border-b-0 border-black px-2">Effectivity Date</td>
                                            <td class="border border-b-0 border-r-0 border-black px-2">APRIL 4, 2023</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr class="h-6">
                    <td :colspan="diets?.length + 1" class="border border-black">
                        <div class="grid grid-cols-4 justify-items-center items-center font-bold">
                            <div class="col-span-1">
                                <span>{{ date }}</span>
                            </div>
                            <div class="col-span-2">
                                <span></span>
                            </div>
                            <div class="col-span-1">
                                <span>{{ meal_time }}{{ setMealTimeSuffix(meal_time) }}</span>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr v-if="diets?.length">
                    <th class="w-48 border border-black text-base">WARD</th>
                    <th class="border border-black text-xs text-pretty px-2">Total No. of Patients</th>
                    <th v-for="diet in diets" class="border border-black text-xs text-pretty px-2">{{ diet }}</th>
                </tr>
            </thead>

            <tbody>
                <template v-for="ward in wards">
                    <tr v-if="ward_grouped?.[ward]?.length" name="table_row" class="border border-black break-inside-avoid break-after-auto font-semibold text-base">
                        <td class="border border-black">
                            <p class="text-left text-sm px-1">{{ ward }}</p>

                            <template v-if="ward_groups[ward]">
                                <p v-for="w in ward_groups[ward].wards" class="m-0 text-sm text-right font-normal px-1">{{ w }}</p>
                            </template>
                        </td>
                        <td class="border border-black">
                            <p class="text-center">{{ ward_grouped?.[ward]?.length }}</p>
                        </td>
                        <td v-for="diet in diets" class="border border-black">
                            <p class="text-center">{{ getWardCount(ward, diet) || '' }}</p>
                        </td>
                    </tr>
                    
                    <template v-if="miscs['room_type']">
                        <tr v-for="room_type in getWardRoomTypes(ward)" name="table_row" class="border border-black text-base break-inside-avoid break-after-auto">
                            <td class="border border-black">
                                <p class="text-end text-sm px-1 italic">{{ room_type }}</p>
                            </td>
                            <td class="border border-black">
                                <p class="text-center">{{ getWardRoomTypeTotal(ward, room_type) }}</p>
                            </td>
                            <td v-for="diet in diets" class="border border-black">
                                <p class="text-center">{{ getWardRoomTypeCount(ward, room_type, diet) || '' }}</p>
                            </td>
                        </tr>
                    </template>
                </template>

                <tr name="table_row" class="border border-black break-inside-avoid break-after-auto text-base font-bold">
                    <td class="border border-black">
                        <p class="text-left px-1">TOTAL</p>
                    </td>
                    <td class="border border-black">
                        <p class="text-center">{{ filtered?.length }}</p>
                    </td>
                    <td v-for="diet in diets" class="border border-black">
                        <p class="text-center">{{ getWardTotal(diet) }}</p>
                    </td>
                </tr>

                <template v-if="miscs['room_type']">
                    <tr v-for="room_type in room_types" name="table_row" class="border border-black break-inside-avoid break-after-auto">
                        <td class="border border-black">
                            <p class="text-end text-sm px-1 italic">{{ room_type }}</p>
                        </td>
                        <td class="border border-black">
                            <p class="text-center">{{ getRoomTypeTotal(room_type) || '' }}</p>
                        </td>
                        <td v-for="diet in diets" class="border border-black">
                            <p class="text-center">{{ getDietRoomTypeCount(room_type, diet) || '' }}</p>
                        </td>
                    </tr>
                </template>
            </tbody>

            <tfoot class="hidden print:table-footer-group">
                <tr>
                    <td :colspan="diets?.length + 2">
                        <ClientOnly>
                            <p class="text-right text-sm italic">Printed {{ new Date() }}</p>
                        </ClientOnly>
                    </td>
                </tr>
            </tfoot>
        </table>

        <div class="fixed bottom-40 right-10 z-10 print:hidden">
            <MealCensusSettings 
                :miscs="miscs" 
                :diets="diets_selected?.[diet_group]" 
                :groups="ward_groups" 
                :wards="wards_checkbox" 
                :diet-group="diet_group" 
                @diets-updated="setDiets()" 
                @groups-updated="setWards()" 
                @miscs-updated="setDiets(); setWards()" 
            />
        </div>
    </ViewTemplate>
</template>