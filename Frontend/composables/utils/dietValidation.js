import { computed } from "vue";

export const useDietValidation = () => {

  //  Checking for Priority Doctors Order
  function isPriority (current_diet, selected_diet, selected_group) {
    const diet_types = ['14','15','17','18'];

    if(current_diet){
      return diet_types.includes(selected_diet) || current_diet.includes('16') || selected_group === '2';
      
    } else {
      return diet_types.includes(selected_diet) || selected_group === '2' ;
    }
  }

  //  Check if doctor's order is within grace period
  function isWithinGracePeriod() {
    const date = new Date();

    // Function to check if current time is within a given time range
    const isTimeInRange = (start_hour, start_minute, end_hour, end_minute) => {
        const start_time = new Date();
        start_time.setHours(start_hour, start_minute, 0);

        const end_time = new Date();
        end_time.setHours(end_hour, end_minute, 0);
        
        return date >= start_time && date <= end_time;
    };

    // Check if the current time is within any of the grace periods
    const grace_period = computed(() => {
        return (
            isTimeInRange(4, 30, 6, 30)   ||  // Breakfast
            isTimeInRange(10, 0, 11, 30)  ||  // Lunch
            isTimeInRange(15, 0, 17, 30)      // Dinner
        );
    });

    return grace_period.value; // Assuming `computed` returns an object with a `value` property
  }


  return { isPriority, isWithinGracePeriod };
}
