export function useEnteralFeeding() {
    const setMealTimeSuffix = (mealtime) => {
        if(['4', '5', '6', '7', '8','9','0'].includes(mealtime.at(-1)) || mealtime.at(-2) === '1') {
            return 'th Feeding';
        }

        const suffixes = {
            1: 'st Feeding',
            2: 'nd Feeding',
            3: 'rd Feeding',
        }

        return suffixes[mealtime.at(-1)];
    }

    return { setMealTimeSuffix };
  }