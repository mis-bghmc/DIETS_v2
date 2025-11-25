<script setup>
const props = defineProps({
    miscs: Object,
    diets: Object,
    groups: Object,
    wards: Array,
    dietGroup: String
});

const emit = defineEmits(['groupsUpdated', 'miscsUpdated', 'dietsUpdated']);

const miscs = ref(props.miscs);

const diets_selected = ref(props.diets);

const group_name = ref('');
const groups = ref(props.groups ?? {});
const wards_checkbox = ref(props.wards);
const wards_selected = ref([]);
const visible = ref(false);


//  Create ward grouping
function createGroup(){
    groups.value[group_name.value] = {
        groupname: group_name.value,
        wards: wards_selected.value
    };

    group_name.value = '';
    wards_selected.value = [];
    
    checkWards();
    emit('groupsUpdated');
}

//  Check for wards that are already in a group
function checkWards(){
    wards_checkbox.value = wards_checkbox.value?.filter(
        (w) => !Object.values(groups.value)?.some((group) => group.wards?.includes(w))
    );
}

//  Remove ward group
function removeGroup(_groupname){
    const wards = [...groups.value[_groupname].wards];

    wards.forEach(() => removeWard(_groupname, 0));

    delete groups.value[_groupname];

    emit('groupsUpdated');
}

//  Remove ward from a group
function removeWard(_groupname, _index){
    const wardname = groups.value[_groupname].wards.splice(_index, 1)[0];
    wards_checkbox.value.push(wardname);
    
    wards_checkbox.value.sort();
    emit('groupsUpdated');
}

//  Disable create button if requirements are not met
function validate() {
    return !group_name.value?.trim() || !wards_selected.value.length || groups.value[group_name.value];
}
</script>

<template>
    <button class="h-24 w-24 border-4 border-primary rounded-full bg-primary-50 shadow-2xl" @click="visible = true">
        <i class="pi pi-cog !text-5xl text-primary"></i>
    </button>

    <Dialog 
        modal 
        v-model:visible="visible" 
        pt:root:class="!w-11/12 lg:!w-1/2 !rounded-md !border-2 !border-primary sm:!text-sm md:!text-base lg:!text-lg"
        pt:header:class="!pb-2 !pt-3 !rounded-t-lg !border-b !border-primary"
        pt:mask:class="!backdrop-blur-sm" 
    >
        <template #header>
            <span class="font-bold text-primary text-2xl">Settings</span>
        </template>

        <!-- Miscellaneous -->
        <Divider align="left" type="dashed">
            <span class="text-primary font-bold">Miscellaneous</span>
        </Divider>

        <div class="flex flex-col gap-2 pr-2 pl-14 pb-4">
            <div class="flex">
                <span class="flex-1">Room Type</span>
                <ToggleSwitch v-model="miscs['room_type']" @change="emit('miscsUpdated')" />
            </div>

            <div class="flex">
                <span class="flex-1">Allergies & Precautions</span>
                <ToggleSwitch v-model="miscs['precautions_allergies']" @change="emit('miscsUpdated')" />
            </div>
        </div>

        <!-- Diets -->
         <template v-if="miscs['precautions_allergies']">
             <Divider align="left" type="dashed">
                 <span class="text-primary font-bold">Diets</span>
             </Divider>
     
             <div class="flex flex-col gap-2 pr-2 pl-14 pb-4">
                <div v-for="diet of Object.keys(diets_selected)" class="flex">
                     <span class="flex-1">{{ diet }}</span>
                     <ToggleSwitch v-model="diets_selected[diet]" @change="emit('dietsUpdated')" />
                 </div>
             </div>
         </template>

        <!-- Ward Grouping -->
        <Divider align="left" type="dashed">
            <span class="text-primary font-bold">Wards</span>
        </Divider>
        
        <div class="px-2">
            <div class="grid grid-cols-2">
                <div class="col-span-1">
                    <div class="my-2">
                        <FloatLabel variant="on">
                            <InputText id="group" v-model="group_name" :invalid="groups?.[group_name]" class="w-full rounded-none rounded-tl-md" />
                            <label for="group">Group Name</label>
                        </FloatLabel>
                    </div>
        
                    <div class="h-[50vh] overflow-auto mb-2">
                        <div v-if="!wards_checkbox?.length">
                            <p class="text-center">No Data.</p>
                        </div>
    
                        <div v-for="ward of wards_checkbox" :key="ward" class="flex align-items-center mb-2">
                            <Checkbox 
                                v-model="wards_selected" 
                                :inputId="ward" 
                                :value="ward" 
                                name="ward" 
                                pt:root:class="!w-8 !h-8" 
                                pt:box:class="!w-full !h-full !border !border-primary"
                            />
                            <label :for="ward" class="ml-2 flex items-center">{{ ward }}</label>
                        </div>
                    </div>
                </div>
    
                <div class="col-span-1">
                    <div class="pl-2">
                        <div class="my-2 h-11 flex justify-center items-center font-bold text-primary text-lg border border-b-0 border-primary rounded-tr-md">
                            <span>Groups Created</span>
                        </div>
                    </div>
    
                    <div class="h-[50vh] overflow-auto ml-2 p-2 border border-dashed border-primary">
                        <div v-for="group of groups">
                            <p class="font-bold text-lg flex items-center m-0">
                                <span>
                                    <Button 
                                        text 
                                        icon="pi pi-times" 
                                        class="text-red-500 p-0 rounded-none hover:bg-red-100" 
                                        pt:icon:class="text-lg" 
                                        @click="removeGroup(group.groupname)" />
                                </span>
    
                                <span class="pl-1">{{ group.groupname }}</span>
                            </p>
                            <p v-for="ward of group.wards" class="flex items-center m-0">
                                <span class="pl-7">
                                    <Button 
                                        text 
                                        icon="pi pi-times" 
                                        class="text-red-500 p-0 rounded-none hover:bg-red-100"
                                        @click="removeWard(group.groupname, group.wards.indexOf(ward))" />
                                </span>
    
                                <span class="pl-1">{{ ward }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
    
            <div>
                <Button label="Create" class="w-full rounded-b-md rounded-t-none" :disabled="validate()" @click="createGroup()"></Button>
            </div>
        </div>
    </Dialog>
</template>