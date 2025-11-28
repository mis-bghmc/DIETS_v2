<script setup>
import { DoctorsOrdersService } from '~/services/DoctorsOrdersService';

const toast = useToast();

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const diets_store = useDietsStore();
const { diets, diets_routine, diets_routine_non_breastfeeding, diets_therapeutic, diets_soft, diets_enteral, sns_enteral, feeding_modes } = storeToRefs(diets_store);

const employees_store = useEmployeesStore();
const { allowed_personnel } = storeToRefs(employees_store);

const props = defineProps({
    data: Object
});

const emit = defineEmits(['updated']);

const patient = ref(props.data?.[0]);
const isVisible_dietRequirements = computed(() => !['18', '17', '16'].includes(order.value?.diet_type));
const isAdult = computed(() => patient.value?.patage >= 18);
const isInfant = computed(() => patient.value?.patage < 1);
const isCalories_invalid = computed(() => !isInfant.value && (isVisible_dietRequirements.value && !order.value.calories));
const isProtein_invalid = computed(() => !order.value?.protein && isAdult.value);
const isCarbohydrates_invalid = computed(() => !order.value?.carbohydrates && isAdult.value && order.value?.diet_type !== '34' && order.value?.sns_type.length < 1);
const isFats_invalid = computed(() => !order.value?.fats &&isAdult.value && order.value?.diet_type !== '34' && order.value?.sns_type.length < 1);
const isMacronutrients_invalid = computed(() => (isProtein_invalid.value || isCarbohydrates_invalid.value || isFats_invalid.value) && isAdult.value && isVisible_dietRequirements.value);
const isDietType_invalid = computed(() => !order.value?.diet_type);
const isTherapeuticType_invalid = computed(() => order.value?.diet_type === 'Therapeutic Diets' && !order.value?.diet_type_sub);
const isEnteral_invalid = computed(() => order.value?.category === 'Enteral' && (!order.value?.feeding_mode || !order.value?.feeding_duration || !order.value?.feeding_frequency));
const isSnsType_invalid = computed(() => !!(order.value.sns_type?.length && !order.value.sns_frequency?.length));

const isFormInvalid = computed(() => {

    if (order.value.diet_type === '16') {
        return false;
    }
    
    return (
        isDietType_invalid.value ||
        isTherapeuticType_invalid.value ||
        isCalories_invalid.value ||
        !order.value.dilution ||
        isMacronutrients_invalid.value ||
        isEnteral_invalid.value ||
        !order.value.food_allergies ||
        isSnsType_invalid.value ||
        hasError_foodAllergies.value ||
        hasError_foodPrecautions.value
    );

});



const isVisible_newOrder = ref(false);
const isVisible_unexpected = ref(false);
const isVisible_draft = ref(false);
const isVisible_subType = ref(false);
const sns_count = ref(0);

// Constants
const LOCAL_STORAGE_KEY = 'diet-form';
const SNS_TIMES = ["AM Snack", "PM Snack", "MN Snack"];

const cutOff_time = ref(getCutOffTime());
const allowedPositions_list = ref([]);

const order = ref();
const draft = ref();
const isLoading_saveDiet = ref(false);
const isLoading_saveDraft = ref(false);
const component_draft = ref();
const a_key = ref();
const p_key = ref();
const use_draft = ref(false);
const hasError_foodAllergies = ref(false);
const hasError_foodPrecautions = ref(false);

const dietTypes_options = ref();
const dietSubTypes_options = ref();
const feedingModes_options = computed(() => {
    if (order.value.diet_type === '34') {
        return feeding_modes.value.filter(item => item.id !== '1');
    } else {
        return feeding_modes.value;
    }
});
// ANCHOR nutrient percentage formula
const nutrientPercentageFormula = ref({
    protein: 15,
    carbohydrates: 55,
    fats: 30
});

const initOrder = () => {
    order.value = {
        category: 'Oral',
        diet_type: null,
        diet_type_sub: null,
        diet_name: null,
        calories: getCalories(),
        protein: null,
        carbohydrates: null,
        fats: null,
        fiber: null,
        dilution: 1,
        feeding_mode: null,
        feeding_duration: null,
        feeding_frequency: null,
        food_allergies: null,
        precautions: null,
        sns_type: [],
        sns_name: [],
        sns_frequency: [],
        sns_description: null,
        remarks: null,
        maternal_status: 'NA'
    }
}

const initDraft = () => {
    draft.value = {
        title: null,
        remarks: null
    }
};

//  Save doctor's order
async function saveOrder() {
    isLoading_saveDiet.value = true;

    const dietcode = order.value.diet_type_sub ?? order.value.diet_type;

    order.value.diet_type = dietcode;
    order.value.diet_name = diets.value?.find(item => item.dietcode === dietcode)?.dietname;
    order.value.sns_name[0] = diets.value?.find(item => item.dietcode === order.value.sns_type[0])?.dietname;
    order.value.sns_name[1] = diets.value?.find(item => item.dietcode === order.value.sns_type[1])?.dietname;
    order.value.sns_frequency = order.value.sns_frequency?.map(item => item.replace(/ Snack$/, ''));
    
    try{
        await DoctorsOrdersService.saveOrder({
            body: {
                patient: patient.value,
                order: order.value,
                entry_by: user.value.employeeid
            }
        });

        emit('updated');
        isVisible_newOrder.value = false;

        toast.add({ severity: 'success', summary: 'Success!', detail: 'Doctor\'s order for patient diet have been successfully saved.', life: 5000 });

    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [New Doctor\'s Order -> save order]' });

    }finally{
        removeDraft();
        isLoading_saveDiet.value = false;
    }
}

//  Save doctor's order as draft
async function saveDraft() {
    isLoading_saveDraft.value = true;
    
    try{
        await DoctorsOrdersService.saveDraft({
            body: {
                docId: user.value.employeeid,
                draftTitle: draft.value.title,
                draftRemarks: draft.value.remarks,
                draftDetails: order.value
            }
        });

        isVisible_draft.value = false;

        nextTick(async () => {
            await component_draft.value?.refresh();
        });

        toast.add({ severity: 'success', summary: 'Success!', detail: 'Draft have been successfully saved.', life: 5000 });

    }catch(error){
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [New Doctor\'s Order -> save draft]' });
    } finally {
        isLoading_saveDraft.value = false;
    }
}

//  Create new screening
async function create() {
    isVisible_newOrder.value = true;
    isVisible_subType.value = false;
    sns_count.value = 0;
    hasError_foodAllergies.value = false;
    hasError_foodPrecautions.value = false;
    
    if(localStorage.getItem(LOCAL_STORAGE_KEY)) isVisible_unexpected.value = true;
    
    initOrder();
    setNutrients(order.value.calories);
    await getDietTypes();
    setDietTypes();
}

//  Get current cut-off time
function getCutOffTime() {
    const now = new Date();
    const currentHour = now.getHours();
    const currentMinutes = now.getMinutes();

    if (currentHour < 4 || (currentHour === 4 && currentMinutes < 59)) {
        return "Morning";
    } else if (currentHour < 10 || (currentHour === 10 && currentMinutes < 59)) {
        return "Lunch";
    } else if (currentHour < 15 || (currentHour === 15 && currentMinutes < 59)) {
        return "Dinner";
    }
}

//  Calories
function getCalories() {
    const age = patient.value?.patage;
    const sex = patient.value?.patsex;
    
    if (!age || !sex) return null;
    
    const calorieTable = [
        { ageRange: [1, 2], M: 1000, F: 920 },
        { ageRange: [3, 5], M: 1350, F: 1260 },
        { ageRange: [6, 9], M: 1600, F: 1470 },
        { ageRange: [10, 12], M: 2060, F: 1980 },
        { ageRange: [13, 15], M: 2700, F: 2170 },
        { ageRange: [16, 18], M: 3010, F: 2280 }
    ];
    
    const entry = calorieTable.find(row => 
        age >= row.ageRange[0] && age <= row.ageRange[1]
    );
    
    return entry ? entry[sex] : null;
}

// ANCHOR Get nutrient value
function setNutrients(c) {
    if (isInfant.value) return;
    
    order.value.protein = c ? Math.round((c * (nutrientPercentageFormula.value.protein / 100 )) / 4) : null;
    order.value.carbohydrates = c ? Math.round((c * (nutrientPercentageFormula.value.carbohydrates / 100 )) / 4) : null;
    order.value.fats = c ? Math.round((c * (nutrientPercentageFormula.value.fats / 100 )) / 9) : null;
}

//  Set food allergies
function setFoodAllergies(allergies) {
    order.value.food_allergies = Array.isArray(allergies) ? allergies.join(', ') : allergies;
}

//  Set precautions
function setPrecautions(precaution) {
    order.value.precautions = Array.isArray(precaution) ? precaution.join(', ') : precaution;
    use_draft.value = false;
}

//  Get diet types
async function getDietTypes() {
    if(Object.keys(diets.value).length) return;

    try {
        await diets_store.getDiets();

    }catch(error) {
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [New Doctor\'s Order -> Diet types]' });
    }
}

//  Set diet types
function setDietTypes() {
    dietTypes_options.value = diets_enteral.value;

    if(order.value?.category === 'Oral') {
        dietTypes_options.value = patient.value?.patage > 0 || patient.value?.patagemo > 6 ? diets_routine_non_breastfeeding.value : diets_routine.value;
    }
}


function setDietTypesSub() {
    isVisible_subType.value = false;
    order.value.diet_type_sub = null; 
    dietSubTypes_options.value = null;
    order.value.feeding_mode = null;
   
    if(order.value.diet_type === '01') dietSubTypes_options.value = diets_soft.value;
    if(order.value.diet_type === 'Therapeutic Diets') dietSubTypes_options.value = diets_therapeutic.value;
}

function onChangeFeedingModeEvent() { 

    if (order.value.feeding_mode === '1') {
        order.value.feeding_duration = '24';
        order.value.feeding_frequency = '1';
    } else {
        order.value.feeding_duration = null;
        order.value.feeding_frequency = null;
    }
}

//  Change category
function changeCategory() {
    setDietTypes();

    order.value.diet_type = null;
    order.value.diet_type_sub = null;
    order.value.feeding_mode = null;
    isVisible_subType.value = false;
}

//  Add SNS
function addSNS() {
    if(sns_count.value < 2) sns_count.value++;
}

//  Remove SNS
function removeSNS(i) {
    order.value.sns_type?.splice(i, 1);
    sns_count.value--;

    if(!sns_count.value){
        order.value.sns_frequency = [];
        order.value.sns_description = null;
    }
}


//  Remove from local storage
function removeDraft() {
    localStorage.removeItem(LOCAL_STORAGE_KEY);
}

//  Use draft
function useDraft(event = null) {
    isVisible_unexpected.value = false;
    
    const draft_object = JSON.parse(event?.details || localStorage.getItem(LOCAL_STORAGE_KEY));
    order.value = {...draft_object};
    
    setDietTypes();
    setDietTypesSub();
    updateAllergiesPrecautions();
    
    if(draft_object.diet_type_sub){
        isVisible_subType.value = true;
        order.value.diet_type_sub = draft_object.diet_type_sub; 
    }

    sns_count.value = order.value?.sns_type?.length;
}

//  Update allergies and precautions from drafts
function updateAllergiesPrecautions() {
    a_key.value = `A_${new Date()}`;
    p_key.value = `P_${new Date()}`;
    use_draft.value = true;
}

//  Get allowed personnel
async function getAllowedPersonnel() {
    if(Object.keys(allowed_personnel.value)?.length) return;

    try {
        await employees_store.getAllowedPersonnel();

    }catch(error) {
        toast.add({ severity: 'error', summary: 'Error!', detail: 'An error has occured. Please log it into the intranet or call extension 202. [New Doctor\'s Order -> Allowed personnel]' });
    }
}


// ANCHOR Watcher 
watch(
    [order, nutrientPercentageFormula],
    ([new_Order, new_NutrientPercentageFormula]) => {
        if (!isVisible_unexpected.value) localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify({ ...new_Order }));
        setNutrients(new_Order.calories);
    },
    { deep: true }
);

//  On mounted
onMounted(async () => {
    await getAllowedPersonnel();
    
    allowedPositions_list.value = allowed_personnel.value?.map(item => item.title);
});




</script>

<template>
    <div>
        <div v-if="allowedPositions_list?.some(item => user?.postitle?.toUpperCase().includes(item))" class="flex flex-col items-center">
            <Button class="w-full md:w-56 font-bold" :disabled="!patient.height || !patient.weight" v-tooltip.bottom="'Create a new Diet Order for this patient.'" @click="create()" >
                <Icon name="fluent:add-circle-12-filled" size="1.5em"/>
                <label class="hover:cursor-pointer">New Diet Order</label>  
            </Button>
            <span v-if="!patient.height || !patient.weight" class="italic text-sm text-red-400">**No height and/or weight**</span>
        </div>
        <Dialog 
            modal
            v-model:visible="isVisible_newOrder" 
            :dismissableMask=false
            :draggable=false
            pt:root:class="!w-full md:!w-11/12 !border-primary !border-2 !rounded-md sm:!text-sm md:!text-base lg:!text-lg" 
            pt:mask:class="!backdrop-blur-sm" 
            @after-hide="removeDraft()"
        >
            <template #header>
                <div class="flex gap-2">
                    <Icon name="healthicons:cardiogram-e" size="2em" />
                    <div class="flex flex-col gap-1">
                        <span class="font-bold">New Diet Order</span>
                        <p>
                            <span class="text-sm text-muted-color"> Create new Diet Order for </span>
                            <span class="text-sm">{{ patient?.patname }}</span>
                        </p>
                    </div>
                </div>
                
                
            </template>
            
            <div class="grid grid-cols-6 gap-6 text-sm">
                <div class=" col-span-6 lg:col-span-2 ">
                    <div class="sticky top-0 flex flex-col gap-8 p-4 border border-dashed border-primary rounded-md h-max">
                        <Accordion :value="['0','1']" multiple>
                            <AccordionPanel value="0">
                                <AccordionHeader>
                                    <div class="flex justify-start items-center gap-4">
                                        <Icon name="fluent:checkmark-note-20-filled" size="1.5rem"/>
                                        <span class="text-base font-bold"> Reminders</span>
                                    </div>
                                </AccordionHeader>
                                <AccordionContent>
                                    <ul class="flex flex-col gap-2">
                                        <li class="flex justify-start items-center gap-4">
                                            <Icon name="fluent:circle-16-filled" class="bg-primary" size="0.5rem" />
                                            <span>Please fill in all <span class="font-semibold underline">required</span> fields.</span>
                                        </li>
                                        <li class="flex justify-start items-center gap-4">
                                            <Icon name="fluent:circle-16-filled" class="bg-primary" size="0.5rem" />
                                            <span>You may save the form as a 
                                                <span class="hover:cursor-help underline " v-tooltip.bottom="'Saved forms that may be reused anytime.'">draft / template</span> 
                                                for future use.
                                            </span>
                                        </li>
                                        <li class="flex justify-start items-center gap-4">
                                            <Icon name="fluent:circle-16-filled" class="bg-primary" size="0.5rem" />
                                            <span>Review the 
                                                <span class="hover:cursor-help underline" v-tooltip.bottom="'Deadline for ordering each meal.'">cut-off times</span> 
                                                for issuing diet orders.
                                            </span>
                                        </li>
                                    </ul>
                                </AccordionContent>
                            </AccordionPanel>

                            <AccordionPanel value="1">
                                <AccordionHeader> 
                                    <div class="flex justify-start items-center gap-4">
                                        <Icon name="fluent:calendar-clock-16-filled" size="1.5rem"/>
                                        <span class="text-base font-bold"> Cut-off Times</span>
                                    </div>
                                </AccordionHeader>
                                <AccordionContent>
                                    <ul class="flex flex-col gap-2 text-sm">
                                        <li class="flex items-center gap-2" :class="{'text-primary-400': cutOff_time === 'Morning'}">
                                            <Icon name="bi:cloud-sun-fill" class="w-10 lg:text-2xl"/>
                                            <span>Breakfast - 5:00 AM</span>
                                        </li>

                                        <li class="flex items-center gap-2" :class="{'text-primary-400': cutOff_time === 'Lunch'}">
                                            <Icon name="bi:sun-fill" class="w-10 lg:text-2xl" />
                                            <span>Lunch - 11:00 AM</span>
                                        </li>

                                        <li class="flex items-center gap-2" :class="{'text-primary-400': cutOff_time === 'Dinner'}">
                                            <Icon name="bi:moon-fill" class="w-10 lg:text-2xl" />
                                            <span>Dinner - 4:00 PM</span>
                                        </li>
                                    </ul>
                                </AccordionContent>
                            </AccordionPanel>

                            <AccordionPanel value="2" v-if="!isAdult">
                                <AccordionHeader> 
                                    <div class="flex justify-start items-center gap-4">
                                        <Icon name="healthicons:child-care" size="1.5rem"/>
                                        <span class="text-base font-bold"> Macronutrient Distribution</span>
                                    </div>
                                </AccordionHeader>
                                <AccordionContent>
                                    <p class="ml-12"> Acceptable Macronutrient Distribution Ranges (AMDR).</p>
                                    <ul class="flex flex-col gap-2 text-sm">
                                        <li class="flex items-center gap-2">
                                            <Icon name="healthicons:star-small" class="w-10 lg:text-2xl text-primary" />
                                            <span>Carbohydrates: 45% - 60%</span>
                                        </li>

                                        <li class="flex items-center gap-2" >
                                            <Icon name="healthicons:star-small" class="w-10 lg:text-2xl text-primary"/>
                                            <span>Protein: 10% - 20%</span>
                                        </li>

                                        <li class="flex items-center gap-2">
                                            <Icon name="healthicons:star-small" class="w-10 lg:text-2xl text-primary" />
                                            <span>Fats: 30% - 35%</span>
                                        </li>
                                    </ul>
                                </AccordionContent>
                            </AccordionPanel>

                            <AccordionPanel value="3">
                                <AccordionHeader> 
                                    <div class="flex justify-start items-center gap-4">
                                        <Icon name="fluent:drafts-16-filled" size="1.5rem"/>
                                        <span class="text-base font-bold"> Draft Templates</span>
                                    </div>
                                </AccordionHeader>
                                <AccordionContent>
                                    <Drafts ref="component_draft" @use-draft="useDraft" />
                                </AccordionContent>
                            </AccordionPanel>
                        </Accordion>
                    </div>
                </div>

                <!-- FORM -->
                <div class="col-span-6 lg:col-span-4">
                    <div class="flex flex-col gap-4">

                        <!-- #1 -->
                        <div class="flex flex-col gap-4">
                            <div class="flex gap-2 items-center">
                                <div class="w-10 h-10 border border-primary rounded-full flex items-center justify-center">
                                    <span class="font-bold">1</span>
                                </div>
        
                                <span class="font-bold">Category</span>
                            </div>
    
                            <div class="flex gap-12 items-center mx-5 pl-12 pt-4 pb-8 border-primary border-l">
                                <div class="flex gap-2 items-center">
                                    <RadioButton inputId="oral" v-model="order.category" :invalid="!order.category" value="Oral" @change="changeCategory()" />
                                    <label for="oral" class="font-semibold">Oral</label>
                                </div>
    
                                <div class="flex gap-2 items-center">
                                    <RadioButton inputId="enteral" v-model="order.category" :invalid="!order.category" value="Enteral" @change="changeCategory()" />
                                    <label for="enteral" class="font-semibold">Enteral</label>
                                </div>
                            </div>
                        </div>
    
                        <!-- #2 -->
                        <div class="flex flex-col gap-4">
                            <div class="flex gap-2 items-center">
                                <div class="w-10 h-10 border border-primary rounded-full flex items-center justify-center">
                                    <span class="font-bold">2</span>
                                </div>
        
                                <span class="font-bold">Type</span>
                            </div>
    
                            <div class="mx-5 pl-12 pt-4 pb-8 border-primary border-l">
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="col-span-2 md:col-span-1">
                                        <Select 
                                            v-model="order.diet_type"
                                            :options="dietTypes_options"
                                            optionLabel="dietname" 
                                            optionValue="dietcode" 
                                            :invalid="!order.diet_type"
                                            placeholder="Select Diet Type"
                                            class="w-full"
                                            @change="setDietTypesSub()"
                                        />
                                    </div>
                                    
                                    <div v-if="order.diet_type === '01' && !isVisible_subType" class="col-span-2 md:col-span-1" @click="isVisible_subType = true">
                                        <Button outlined icon="pi pi-plus !text-primary" class="!w-full !h-full" />
                                    </div>

                                    <div v-if="isVisible_subType || order.diet_type === 'Therapeutic Diets'" class="col-span-2 md:col-span-1">
                                        <Select 
                                            v-model="order.diet_type_sub"
                                            :options="dietSubTypes_options"
                                            optionLabel="dietname" 
                                            optionValue="dietcode" 
                                            :invalid="order.diet_type === 'Therapeutic Diets' && !order.diet_type_sub"
                                            placeholder="Select Sub-diet Type"
                                            class="w-full"
                                        >
                                            <template #value="slotProps">
                                                <div v-if="slotProps.value" class="flex items-center">
                                                    <div>{{ dietSubTypes_options?.find(item => item.dietcode === slotProps.value)?.dietname }}</div>
                                                </div>
                                                <div v-else>
                                                    <div>{{ slotProps.placeholder }}</div>
                                                </div>
                                                
                                                <i v-if="order.diet_type === '01'" class="pi pi-times p-select-clear-icon" @click="order.diet_type_sub = null; isVisible_subType = false"></i>
                                            </template>
                                        </Select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- #3 -->
                        <div class="flex flex-col gap-4" v-if="isVisible_dietRequirements">
                            <div class="flex gap-2 items-center">
                                <div class="w-10 h-10 border border-primary rounded-full flex items-center justify-center">
                                    <span class="font-bold">3</span>
                                </div>
        
                                <span class="font-bold">Requirements</span>
                            </div>
    
                            <div class="mx-5 pl-12 pt-4 pb-8 border-primary border-l">
                                <div class="flex flex-col md:flex-row gap-2">
                                    <IftaLabel class="w-full md:w-56">
                                        <InputNumber id="calories" v-model="order.calories" :min="0.01" :maxFractionDigits="2" :invalid="isCalories_invalid" class="w-full" />
                                        <label for="calories">Calories (kcal)</label>
                                    </IftaLabel>

                                    <IftaLabel v-if="order.category === 'Enteral'" class="w-full md:w-56">
                                        <InputNumber id="dilution" v-model="order.dilution" :min="1" :maxFractionDigits="2" :invalid="!order.dilution" class="w-full" />
                                        <label for="dilution">Dilution (1 kcal : 1 ml)</label>
                                    </IftaLabel>
                                </div>

                                <Divider v-if="order.category === 'Enteral'" type="dashed" />

                                <div v-if="order.category === 'Enteral'" class="flex flex-col md:flex-wrap lg:flex-row gap-2">
                                    <IftaLabel class="w-full md:w-[28rem] lg:w-56">
                                        <Select 
                                            id="feeding_mode"
                                            v-model="order.feeding_mode"
                                            :options="feedingModes_options"
                                            optionLabel="name" 
                                            optionValue="id" 
                                            :invalid="!order.feeding_mode"
                                            class="w-full"
                                            @change="onChangeFeedingModeEvent()"
                                        />
                                        <label for="feeding_mode">Feeding Mode</label>
                                    </IftaLabel>
                                    <div class="flex flex-col md:flex-row gap-2 w-full">
                                        <IftaLabel class="w-full md:w-56">
                                            <InputNumber id="feeding_duration" v-model="order.feeding_duration" :min="1" :max="24" :invalid="!order.feeding_duration" class="w-full" suffix=" hrs"  :disabled="order.feeding_mode === '1'"/>
                                            <label for="feeding_duration">Feeding Duration</label>
                                        </IftaLabel>
    
                                        <IftaLabel class="w-full md:w-56">
                                            <InputNumber id="feeding_frequency" v-model="order.feeding_frequency" :min="1" :invalid="!order.feeding_frequency" class="w-full" suffix=" feeding/s per day" :disabled="order.feeding_mode === '1'"/>
                                            <label for="feeding_frequency">Feeding Frequency</label>
                                        </IftaLabel>
                                    </div>
                                </div>
                                
                                <Divider type="dashed" />
                                <!-- ANCHOR Nutrients form -->
                                <div class="flex flex-col md:flex-wrap lg:flex-row gap-2" v-if="isAdult">
                                    <div class="flex flex-col md:flex-row gap-2 w-full">
                                        <IftaLabel class="w-full md:w-56">
                                            <InputNumber id="protein" v-model="order.protein" :min="0.01" :maxFractionDigits="2" :invalid="isProtein_invalid" class="w-full" />
                                            <label for="protein">Protein (g)</label>
                                        </IftaLabel>
    
                                        <IftaLabel class="w-full md:w-56">
                                            <InputNumber id="carbohydrates" v-model="order.carbohydrates" :min="0.01" :maxFractionDigits="2" :invalid="isCarbohydrates_invalid" class="w-full" />
                                            <label for="carbohydrates">Carbohydrates (g)</label>
                                        </IftaLabel>
                                    </div>

                                    <div class="flex flex-col md:flex-row gap-2 w-full">
                                        <IftaLabel class="w-full md:w-56">
                                            <InputNumber id="fats" v-model="order.fats" :min="0.01" :maxFractionDigits="2" :invalid="isFats_invalid" class="w-full" />
                                            <label for="fats">Fats (g)</label>
                                        </IftaLabel>
    
                                        <IftaLabel class="w-full md:w-56">
                                            <InputNumber id="fiber" v-model="order.fiber" :min="0.01" :maxFractionDigits="2" class="w-full" />
                                            <label for="fiber">Fiber (g)</label>
                                        </IftaLabel>
                                    </div>
                                </div>
                                <div class="flex flex-col md:flex-wrap lg:flex-row gap-2" v-else>
                                    <div class="flex flex-col md:flex-row gap-2 w-full">
                                        <IftaLabel class="w-full md:w-56">
                                            <InputNumber id="protein" v-model="nutrientPercentageFormula.protein" :min="10" :max="20" suffix=" %"  :invalid="isProtein_invalid" class="w-full" @change="setNutrients()"/>
                                            <label for="protein">Protein <span class="font-bold text-primary"> ({{ order.protein }} g)</span></label>
                                        </IftaLabel>
    
                                        <IftaLabel class="w-full md:w-56">
                                            <InputNumber id="carbohydrates" v-model="nutrientPercentageFormula.carbohydrates" :min="45" :max="60" suffix=" %"  :invalid="isCarbohydrates_invalid" class="w-full" @change="setNutrients()"/>
                                            <label for="carbohydrates">Carbohydrates <span class="font-bold text-primary"> ({{ order.carbohydrates }} g)</span> </label>
                                        </IftaLabel>
                                    </div>

                                    <div class="flex flex-col md:flex-row gap-2 w-full">
                                        <IftaLabel class="w-full md:w-56">
                                            <InputNumber id="fats" v-model="nutrientPercentageFormula.fats" :min="30" :max="35" suffix=" %"  :invalid="isFats_invalid" class="w-full" @change="setNutrients()"/>
                                            <label for="fats">Fats <span class="font-bold text-primary"> ({{ order.fats }} g)</span></label>
                                        </IftaLabel>
    
                                        <IftaLabel class="w-full md:w-56">
                                            <InputNumber id="fiber" v-model="nutrientPercentageFormula.fiber" :min="0.01" :maxFractionDigits="2" class="w-full" />
                                            <label for="fiber">Fiber (g)</label>
                                        </IftaLabel>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- #4 -->
                        <div class="flex flex-col gap-4" v-if="order.diet_type !== '16'">
                            <div class="flex gap-2 items-center">
                                <div class="w-10 h-10 border border-primary rounded-full flex items-center justify-center">
                                    <span class="font-bold">{{ isVisible_dietRequirements ? '4' : '3' }}</span>
                                </div>
        
                                <span class="font-bold">Restrictions</span>
                            </div>
    
                            <div class="mx-5 pl-12 pt-4 pb-8 border-primary border-l">
                                <div class="flex flex-col lg:flex-row gap-4 lg:gap-0">
                                    <div class="w-full lg:w-1/2">
                                        <div class="flex flex-col gap-2">
                                            <div class="text-left">
                                                <span class="text-sm font-bold">Allergies</span>
                                            </div>

                                            <Allergies :data="use_draft ? order.food_allergies : patient?.category" :key="a_key" @updated="setFoodAllergies" @error="hasError_foodAllergies = true" />
                                        </div>
                                    </div>

                                    <Divider type="dashed" layout="vertical" class="hidden lg:block" />

                                    <div class="w-full lg:w-1/2">
                                        <div class="flex flex-col gap-2">
                                            <div class="text-left">
                                                <span class="text-sm font-bold">Precautions</span>
                                            </div>
    
                                            <Precautions :data="use_draft ? order.precautions : patient?.precaution" :key="p_key" @updated="setPrecautions" @error="hasError_foodPrecautions = true" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- #5 -->
                        <div class="flex flex-col gap-4" v-if="order.diet_type !== '16'">
                            <div class="flex gap-2 items-center">
                                <div class="w-10 h-10 border border-primary rounded-full flex items-center justify-center">
                                    <span class="font-bold">{{ isVisible_dietRequirements ? '5' : '4' }}</span>
                                </div>
        
                                <span class="font-bold">Special Nutrition Supplement (SNS)</span>
                            </div>
    
                            <div class="mx-5 pl-12 pt-4 pb-8 border-primary border-l">
                                <div class="flex flex-col gap-2">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div v-for="i = 0 in sns_count" class="col-span-2 md:col-span-1">
                                            <IftaLabel>
                                                <Select 
                                                    id="sns_type"
                                                    v-model="order.sns_type[i-1]"
                                                    :options="sns_enteral"
                                                    optionLabel="dietname" 
                                                    optionValue="dietcode" 
                                                    :invalid="!order.sns_type[i-1]"
                                                    showClear
                                                    class="w-full h-16"
                                                >
                                                    <template #value="slotProps">
                                                        <div v-if="slotProps.value" class="flex items-center">
                                                            <div>{{ sns_enteral?.find(item => item.dietcode === slotProps.value)?.dietname }}</div>
                                                        </div>
                                                        
                                                        <i class="pi pi-times p-select-clear-icon" @click="removeSNS(i-1)"></i>
                                                    </template>

                                                    <template #clearicon="{ clearCallback }">
                                                        <i class="pi pi-times p-select-clear-icon" @click="clearCallback; removeSNS(i-1)"></i>
                                                    </template>
                                                </Select>
                                                <label for="sns_type">Type</label>
                                            </IftaLabel>
                                        </div>
    
                                        <div v-if="sns_count < 2" class="col-span-2 md:col-span-1 h-16" @click="addSNS()">
                                            <Button outlined icon="pi pi-plus !text-primary" class="!w-full !h-full" />
                                        </div>
                                    </div>

                                    <div v-if="order.sns_type?.[0]" class="flex flex-col gap-2">
                                        <IftaLabel class="w-full">
                                            <MultiSelect 
                                                id="sns_frequency"
                                                v-model="order.sns_frequency" 
                                                :options="SNS_TIMES" 
                                                display="chip" 
                                                scrollHeight="50vh" 
                                                :invalid="!order.sns_frequency?.length" 
                                                class="w-full h-16"
                                                pt:labelContainer:class="flex items-end"
                                            />
                                            <label for="sns_frequency">Frequency</label>
                                        </IftaLabel>
    
                                        <IftaLabel class="w-full">
                                            <Textarea id="sns_description" v-model="order.sns_description" class="w-full min-h-16" />
                                            <label for="sns_description">Description</label>
                                        </IftaLabel>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- #6 -->
                        <div class="flex flex-col gap-4">
                            <div class="flex gap-2 items-center">
                                <div class="w-10 h-10 border border-primary rounded-full flex items-center justify-center">
                                    <span class="font-bold">{{ isVisible_dietRequirements ? '6' : order.diet_type === '16' ? '3' : '5' }}</span>
                                </div>
        
                                <span class="font-bold">Remarks </span>
                            </div>
    
                            <div class="mx-5 pl-12 pt-4 pb-8 border-primary border-l">
                                <Textarea id="remarks" v-model="order.remarks" class="w-full min-h-16" />
                            </div>
                        </div>

                        <!-- #7 -->
                        <div v-if="patient?.patsex === 'F'" class="flex flex-col gap-4">
                            <div class="flex gap-2 items-center">
                                <div class="w-10 h-10 border border-primary rounded-full flex items-center justify-center">
                                    <span class="font-bold">7</span>
                                </div>
        
                                <span class="font-bold">Maternal Status</span>
                            </div>
    
                            <div class="flex flex-col md:flex-row gap-4 md:gap-12 items-start md:items-center mx-5 pl-12 pt-4 pb-8 border-primary border-l">
                                <div class="flex gap-2 items-center">
                                    <RadioButton inputId="pregnant" v-model="order.maternal_status" value="PREGNANT" />
                                    <label for="pregnant" class="font-semibold">Pregnant</label>
                                </div>
    
                                <div class="flex gap-2 items-center">
                                    <RadioButton inputId="lactating" v-model="order.maternal_status" value="LACTATING" />
                                    <label for="lactating" class="font-semibold">Lactating</label>
                                </div>

                                <div class="flex gap-2 items-center">
                                    <RadioButton inputId="not_applicable" v-model="order.maternal_status" value="NA" />
                                    <label for="not_applicable" class="font-semibold">Not Applicable</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <template #footer>
                <div class="flex gap-4 items-center justify-end pt-3 prime-font-bold">
                    <Button label="Save as Draft/Template" icon="pi pi-file" severity="info" variant="outlined" @click="initDraft(); isVisible_draft = true" />
                    <Button :disabled="isFormInvalid || isLoading_saveDiet" icon="pi pi-save" label="Issue Diet Order" severity="primary" @click="saveOrder()" :loading="isLoading_saveDiet" />
                </div>
            </template>
        </Dialog>

        <!-- SAVE DRAFT -->
        <Dialog 
            modal
            v-model:visible="isVisible_draft" 
            :dismissableMask=false
            :draggable=false
            pt:root:class="!w-full md:!w-2/3 lg:!w-1/3 !h-max !border-primary !border-2 !rounded-lg sm:!text-sm md:!text-base lg:!text-lg" 
            pt:mask:class="!backdrop-blur-sm" 
        >
            <template #header>
                <span class="font-bold text-xl">Save as Draft/Template </span>
            </template>
            <div class="flex flex-col gap-6 text-base font-bold">
                <div class="flex flex-col">
                    <span class="mx-2">Title</span>
                    <InputText v-model="draft.title" :maxlength="50" :invalid="!draft.title" class="ml-2"/>
                </div>

                <div class="flex flex-col">
                    <span class="mx-2">Remarks</span>
                    <Textarea v-model="draft.remarks" class="min-h-16 ml-2" />
                </div>
            </div>
            <template #footer>
                <div class="w-full">
                    <Divider />
                    <div class="flex justify-end gap-4 prime-font-bold">
                        <Button outlined  @click="isVisible_draft = false" severity="danger" label="Cancel">
                            <template #icon>
                                <Icon name="fluent:arrow-circle-left-12-filled" size="1.5rem" />
                            </template>
                        </Button>
                        <Button outlined  :disabled="!draft.title || isLoading_saveDraft" @click="saveDraft()" label="Confirm Save" :loading="isLoading_saveDraft">
                            <template #icon>
                                <Icon name="fluent:save-16-filled" size="1.5rem" />
                            </template>
                        </Button>
                    </div>
                </div>
                
            </template>
        </Dialog>

        <!-- UNEXPECTED CLOSURE -->
        <Dialog 
            modal
            v-model:visible="isVisible_unexpected" 
            :dismissableMask=false
            :draggable=false
            pt:root:class="!w-2/3 lg:!w-1/3 !h-max !border-primary !border-2 !rounded-md sm:!text-sm md:!text-base lg:!text-lg" 
            pt:mask:class="!backdrop-blur-sm" 
        >     
            <template #container>
                <div class="flex flex-col gap-4 p-6">
                    <div class="flex flex-col gap-4">
                        <span class="font-bold text-lg"> Saved Form</span>
                        <span class="text-base">A previous entry was saved due to unexpected closure. <br> Do you wish to use it?</span>
                    </div>

                    <div class="flex justify-end gap-4">
                        <Button label="No" outlined class="w-20 p-button-danger"  @click="isVisible_unexpected = false" />
                        <Button label="Yes" outlined class="w-20 " @click="useDraft()" />
                    </div>
                </div>
            </template>
        </Dialog>
    </div>
</template>

<style scoped>

.prime-font-bold {
    --p-button-label-font-weight: 600;
}

</style>