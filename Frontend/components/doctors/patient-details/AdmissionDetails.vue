<script setup>
import MaleAvatar from '~/public/images/male-avatar.svg';
import FemaleAvatar from '~/public/images/female-avatar.svg';

const { formatDateTime } = useDate();

const props = defineProps({
    data: Object
});

const patient = ref(props.data?.[0]);
const patientAgeBracket = computed(() => Number(patient.value?.patage) > 18 ? 'Adult' : 'Pedia');
const patientGender = computed(() => patient.value?.patsex === 'M' ? 'Male' : 'Female');
const patientAge = computed(() => Number(patient.value?.patage) > 0 ? `${Number(patient.value?.patage)} y/o` : `${Number(patient.value?.patagemo)} m/o`);
const patientHpercode = computed(() => formatHpercode(patient.value?.hpercode));
const patientAdmissionDate = computed(() => formatDateTime(patient.value?.admdate));

//  Format hpercode
function formatHpercode(h) {
    return h.replace(/^0+/, '');
}
</script>

<template>
    <div>
        <div>
            <h5 class="flex gap-2 items-center"> 
                <Icon name="fluent:patient-32-filled" size="1.5em" class="text-primary"/> 
                <span class="font-bold">Patient Details</span>
            </h5>
        </div>

        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-3 gap-4 items-center">
                <div class="col-span-1 relative w-full">
                    <MaleAvatar v-if="patient?.patsex === 'M'" class="text-primary" />
                    <FemaleAvatar v-else class="text-primary" />
                </div>

                <div class="col-span-2 text-left">
                    <div class="flex flex-col gap-2">
                        <div class="flex flex-wrap gap-2">
                            <Tag v-if="patient?.patage !== null" class="w-16" severity="success">
                                <span class="text-xs">{{ patientAgeBracket }}</span>
                            </Tag>

                            <Tag v-if="patient?.matstatus && patient?.matstatus !== 'NA'" severity="info" class="w-20">
                                <span class="text-xs">{{ patient?.matstatus }}</span>
                            </Tag>

                            <Tag v-if="patient?.transfer_status && patient?.transfer_status === 'P'" class="hover:cursor-help" v-tooltip.top="'Pending Acceptance in Ward'">
                                <span class="text-xs">P</span>
                            </Tag>

                            <Tag v-if="patient?.er_transfer_status" class="hover:cursor-help" v-tooltip.top=" patient?.er_transfer_status === 'Y' ? 'Moved from ER to ward' : ''">
                                <span class="text-xs">MTW</span>
                            </Tag>
                            
                        </div>

                        <span class="font-bold text-base lg:text-base">{{ patient?.patname || '-' }}</span>

                        <div class="flex gap-2 font-semibold">
                            <span>{{ patientAge }}</span>
                            <span>|</span>
                            <span>{{ patientGender }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-between">
                <span>
                    <label class="text-muted-color italic text-sm">Hospital #</label>
                    <p class="font-semibold">{{ patientHpercode }}</p>
                </span>

                <span>
                    <label class="text-muted-color italic text-sm">Admission Date</label>
                    <p class="font-semibold">{{ patientAdmissionDate }}</p>
                </span>
            </div>

            <span>
                <label class="text-muted-color italic text-sm">Ward - Room</label>
                <p class="font-semibold">{{ `${patient?.wardname} - ${patient?.rmname}` }}</p>
            </span>

            <span>
                <label class="text-muted-color italic text-sm">Allergies</label>
                <p class="font-semibold">{{ patient?.allergies || '-' }}</p>
            </span>

            <span>
                <label class="text-muted-color italic text-sm">Admission Diagnosis</label>
                <p class="font-semibold">{{ patient?.admtxt || '-' }}</p>
            </span>
        </div>
    </div>
</template>
