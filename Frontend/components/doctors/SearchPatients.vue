<script setup>
import { FilterMatchMode } from '@primevue/core/api';
import NoData from '~/public/images/no-data.svg';

const router = useRouter();
const { sortArray } = useSort();
const { formatMonthShort } = useDate();

const user_store = useUserStore();
const { user } = storeToRefs(user_store);

const patients_store = usePatientsStore();
const { admitted_patients, my_admitted_patients } = storeToRefs(patients_store);

const props = defineProps({
    global: Boolean
});

const { error, status, refresh } = await useAsyncData(
    'admitted-patients', 
    () => patients_store.getAdmittedPatients(),
    {
        default: () => []
    }
);

const { refresh: my_refresh } = await useAsyncData(
    'my-patients', 
    () => patients_store.getMyPatients(user.value?.employeeid),
    {
        default: () => []
    }
);

const patients_all = ref();
const patients = ref();
const wards = ref();
const diets = ref();
const checked_my_patients = ref(false);
const checked_er_boarders = ref(false);
const checked_ward_pending = ref(false);
const checked_mtw = ref(false);

const filters = ref();

const initFilters = () => {
    checked_my_patients.value = false;
    checked_er_boarders.value = false;
    checked_ward_pending.value = false;
    checked_mtw.value = false;

    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS },
        hpercode: { value: null, matchMode: FilterMatchMode.EQUALS },
        patname: { value: null, matchMode: FilterMatchMode.EQUALS },
        dietname: { value: null, matchMode: FilterMatchMode.EQUALS },
        wardname: { value: null, matchMode: FilterMatchMode.EQUALS },
        admdate: { value: null, matchMode: FilterMatchMode.DATE_IS },
        location: { value: null, matchMode: FilterMatchMode.EQUALS },
    };
};

const visible = ref(false);


//  Redirect to patient's diet information page
function redirect(event) {
    visible.value = false;
    
    const enccode = encodeURIComponent(event.data.enccode);

    router.push(`/doctors/patient/${enccode}`);
}

//  
function checkPatientLocation(disnotes, er_status, ward_status) {
    if (!disnotes?.startsWith('ER') || ward_status === 'A') {
        return null;
    }
    const key = `${er_status === 'Y' ? 'Y' : 'N'}_P`;

    return key;
}

//
function customFilter() {
    const filters = [
        checked_er_boarders.value
            ? patient => patient.wardStatus === 'N_P'
            : null,

        checked_mtw.value
            ? patient => patient.er_transfer_status === 'Y'
            : null,

        checked_ward_pending.value
            ? patient => patient.transfer_status === 'P'
            : null,

        checked_my_patients.value
            ? patient => my_admitted_patients.value?.some(p => p.enccode === patient.enccode)
            : null
    ].filter(Boolean); // remove null entries

    // No filters? Show all
    if (filters.length === 0) {
        patients.value = patients_all.value;
        return;
    }

    patients.value = patients_all.value.filter(patient =>
        filters.every(match => match(patient))
    );
}

//  
async function show() {
    visible.value = true;
    await refresh();
    await my_refresh();
}


//  Watcher for data
watch(
    admitted_patients,
    (new_value) => {
        initFilters();
        patients_all.value = sortArray(
            new_value.map(
                item => ({
                    ...item, 
                    admdate: new Date(item.admdate), 
                    wardStatus: checkPatientLocation(item.disnotes, item.er_transfer_status, item.transfer_status),
                    dietname: item.dietname ?? 'No diet'
                })
            ), 
            'patname'
        );
        patients.value = patients_all.value;
        wards.value = Object.keys(Object.groupBy(patients.value ?? [], ({wardname}) => wardname))?.sort() ?? [];
        diets.value = Object.keys(Object.groupBy(patients.value ?? [], ({dietname}) => dietname))?.sort() ?? [];
    },
    { immediate: false }
)

//  Watcher for my custom filter checkboxes
watch(
    [checked_er_boarders, checked_mtw, checked_ward_pending, checked_my_patients],
    (new_value) => {
        const anyChecked = new_value.some(item => item);
        anyChecked ? customFilter() : patients.value = patients_all.value;   
    }
)
</script>

<template>
    <button v-if="global" 
        type="button" 
        class="text-primary border-0 border-primary rounded-full w-10 h-10 pt-1" 
        v-tooltip.bottom="'Search Admitted Patients'"
        @click="show()" 
    >
        <i class="pi pi-search !text-4xl !text-primary layout-topbar-logo"></i>
    </button>

    <Button v-else text icon="pi pi-search" @click="show()" />

    <Dialog 
        modal
        v-model:visible="visible" 
        :dismissableMask=false
        :draggable=false
        pt:root:class="!w-full md:!w-11/12 !h-full !border-primary !border-2 !rounded-md sm:!text-sm md:!text-base lg:!text-lg" 
        pt:header:class="!pb-0 !pt-0 !border-b !border-primary"
        pt:content:class="!p-0"
        pt:mask:class="!backdrop-blur-sm" 
    >
        <template #header>
            <div class="flex my-5 gap-2 items-center">
                <Icon name="healthicons:observation" size="2.5em" />
                <div class="flex flex-col gap-2">
                    <span class="font-bold">Admitted Patients</span>
                    <span class="text-muted-color text-sm"> List of Admitted Patients to the Hospital  
                        <span class="italic"> (Click a patient to view Diet Details)</span>
                    </span>
                </div>
            </div>
        </template>

        <ViewTemplate :error="error" :status="status">
            <DataTable 
                :value="patients" 
                datakey="hpercode"
                stripedRows 
                :rowHover="true" 
                paginator
                paginatorTemplate="RowsPerPageDropdown FirstPageLink PrevPageLink CurrentPageReport NextPageLink LastPageLink"
                currentPageReportTemplate="{first} to {last} of {totalRecords}"
                :rows="10"
                :rowsPerPageOptions="[10, 20, 50]" 
                :pageLinkSize="1" 
                v-model:filters="filters"
                filterDisplay="menu"
                :globalFilterFields="['patname', 'dietname', 'hpercode', 'wardname', 'admdate']"
                pt:root:class="sm:text-xs md:text-xs lg:text-base hover:cursor-pointer"
                scrollable scrollHeight="60vh"
                removable-sort
                @row-click="redirect"
            >
                <template #header v-if="admitted_patients">
                    <div class="flex items-start gap-4 py-4">
                        <div class="grid grid-cols-2 gap-2 w-full">
                            <div class="col-span-2 md:col-span-1 flex gap-4">
                                <Button outlined class="!py-0 !px-5">
                                    <Icon name="healthicons:female-and-male" size="2em" class="text-primary"/>
                                    <span class="font-bold text-center">{{ admitted_patients.length }}</span>
                                </Button>

                                <div class="flex items-center gap-2">
                                    <Checkbox id="my_patients" v-model="checked_my_patients" binary class="!h-6 !w-6" pt:box:class="!h-full !w-full !rounded-full" />
                                    <label for="my_patients" v-tooltip.bottom="'Patients with Doctor’s Orders Issued by the User '">My Patients</label>
                                </div>
                            </div>

                            <div class="col-span-2 md:col-span-1 flex gap-4 justify-self-end w-full">
                                <IconField iconPosition="left" class="w-full">
                                    <InputIcon>
                                        <i class="pi pi-search" />
                                    </InputIcon>
                                    <InputText v-model="filters['global'].value" placeholder="Search..." class="w-full flex items-center" />
                                </IconField>

                                <Button outlined icon="pi pi-filter-slash" label="Clear" class="!py-0 !px-5" v-tooltip.bottom="'Clear all filters'" @click="initFilters()" />
                            </div>
                        </div>
                    </div>
                </template>

                <template #empty>
                    <div class="container mx-auto flex flex-col justify-center items-center py-4 h-[50vh]">
                        <NoData class="text-primary" />
                        <p class="text-lg font-bold mt-4">No data found.</p>
                    </div>
                </template>

                <Column field="hpercode" header="Hospital #" style="width: 10%" />

                <Column field="patname" header="Name" style="width: 30%" />

                <Column field="wardname" header="Wards" :showFilterMatchModes="false" style="width: 30%">
                    <template #filter="{ filterModel, applyFilter }">
                        <Select v-model="filterModel.value" :options="wards" class="p-column-filter" style="min-width: 12rem" placeholder="Filter by ward.." @change="applyFilter()" />

                        Additional Filters: 

                        <div class="ml-4 flex flex-col gap-2 text-sm">
                            <span class="flex items-center gap-2"> 
                                <Checkbox v-model="checked_er_boarders" binary />
                                <label class="hover:cursor-help" v-tooltip.bottom="'Admitted patients who remain in the emergency room.'"> ER Boarders </label>
                            </span>

                            <span class="flex items-center gap-2"> 
                                <Checkbox v-model="checked_mtw" binary />
                                 <label class="hover:cursor-help" v-tooltip.bottom="'Patients marked by ER as “Moved to Ward” (already transferred out of ER)'"> Moved to Ward </label>
                            </span>
                            <span class="flex items-center gap-2"> 
                                <Checkbox v-model="checked_ward_pending" binary/>
                                 <label class="hover:cursor-help" v-tooltip.bottom="'Admitted patients but are not yet accepted in Ward'"> Pending Ward </label>
                            </span>
                        </div>
                        
                    </template>
                    <template #filterapply />
                    <template #body="{data, field}">
                        <span class="prime-tag">
                            {{ data[field] }}
                            <Tag v-if="data?.wardStatus" 
                                :severity="data?.wardStatus === 'N_P' ? 'warn' : 'danger'" 
                                :value="data?.wardStatus === 'N_P' ? 'ER Boarder - P' : '? Unknown'"
                                v-tooltip.bottom="data?.wardStatus === 'N_P'
                                    ? 'ER Boarder: Patient was not tagged as Moved to Ward and is not yet accepted in Ward ' 
                                    : 'Location Unknown: Patient was tagged as Moved to Ward but is not yet accepted in Ward'"
                                class="text-xs" 
                                />
                            </span>
                    </template>
                </Column>

                <Column field="dietname" header="Diets" :showFilterMatchModes="false" showClearButton style="width: 20%">
                    <template #filter="{ filterModel, applyFilter }">
                        <Select v-model="filterModel.value" :options="diets" class="p-column-filter" style="min-width: 12rem" placeholder="Filter by diet type" @change="applyFilter()" />
                    </template>
                    <template #filterapply />
                </Column>

                <Column field="admdate" header="Admission Date" :showFilterMatchModes="false" sortable showClearButton style="width: 10%" >
                    <template #body="{data, field}">
                        <span>{{ formatMonthShort(data[field]) }}</span>
                    </template>

                    <template #filter="{ filterModel, applyFilter }">
                        <DatePicker v-model="filterModel.value" placeholder="mm/dd/yyyy" @date-select="applyFilter()"/>
                    </template>
                    <template #filterapply />
                </Column>
            </DataTable>
        </ViewTemplate>
    </Dialog>
</template>

<style scoped>
    .prime-tag {
        	
        --p-tag-padding : 0.1rem 0.5rem;
        --p-tag-font-size : 0.8rem;
    }

    .prime-datatable{
        border: 1px solid #c8c8c8;
        border-top: none; /* remove top border only */
        border-radius: 0 0 10px 10px;
        padding: 12px;
    }

</style>