export interface doctorsOrdeInputInterface {

    dietCategory: '1' | '2',
    dietType1: String | null,
    dietType2: String | null,
    dietCalories: String | null,
    dietVolume: String | null,
    dietDilution: String | null,
    nutrientsProtein: String | null,
    nutrientsCarbohydrates: String | null,
    nutrientsFat: String | null,
    nutrientsFiber: String | null,
    feedingMode: String | null,
    feedingDuration: String | null,
    feedingFrequency: String | null,
    allergyType: String | null,
    allergySubtype: String | null,
    precautions: String | null,
    snsType: String | null,
    snsFrequency: Array<String> | null,
    snsDescription: String | null,
    remarks: String | null,
    age: String | null,
    gender: String | null

}

export interface validationResult {
    isValid: boolean,
    errors: {
        field: string,
        message: string
    }[];
}


export interface errorFieldsInterface {
    diet: string,
    message: string
}


export interface requiredBodyInterface {
    hpercode: string,
    enccode: string,
    entryBy: string,
    remarks: string,
    dietCode1: string,
    dietCode2: string,
    protein: string,
    fiber: string,
    calories: string,
    dilution: string,
    carbohydrates: string,
    fats: string,
    sodium: string,
    volume: string,
    licno: string,
    allergy: string,
    onsFrequency: string,
    onsType: string,
    onsDescription: string,
    feedingMode: string,
    feedingDuration: string,
    feedingFrequency: string,
    priority: string,
    onsType2: string,
    precaution: string,
    reproductiveStat: string,
    previousDiet: string                        // Old Diet name used for showing what was selected and updated. 
}

export interface cookieUserInterface {
    employeeid: string,
    user_name: string,
    user_level: string,
    firstname: string,
    lastname: string,
    middlename: string,
    posttitle: string,
    maxAge: number
}

export interface nutritionScreeningInterface {
    screeningAnswers: Array<string>,
    enccode: string,
    height: string,
    weight: string,
    bmi: string,
    entry_by: string,
    age: string
}



export interface doctorsOrderFormInterface {
    dietCategory: '1' | '2',
    dietType1: string | null,
    dietType2: string | null,
    dietCalories: string | null,
    dietVolume: string | null,
    dietDilution: string | null,
    nutrientsProtein: number | null | undefined,
    nutrientsCarbohydrates: number | null | undefined,
    nutrientsFat: number | null | undefined,
    nutrientsFiber: number | null | undefined,
    feedingMode: string | null,
    feedingDuration: string | null,
    feedingFrequency: string | null,
    allergyType: string | null,
    allergySubtype: string | null,
    precautions: string | null,
    snsType: string | null,
    snsFrequency: Array<string> | null,
    snsDescription: string | null,
    remarks: string | null
}


export interface interface_userFetchResult {
    employeeid: string,
    user_name: string,
    user_level: string,
    firstname: string,
    lastname: string,
    middlename: string,
    postitle: string
}


export interface user_cookie {
    employeeid: string;
    user_name: string;
    user_level: keyof typeof ALLOWED_PAGES | '';
    firstname: string;
    lastname: string;
    middlename: string;
    postitle: string;
}

const ALLOWED_PAGES = {
    '1': ['/admin', '/doctor', '/dietary', '/foodserver', '/nurse'],
    '0': ['/doctor'],
    '59': ['/dietary', '/foodserver'],
    '60': ['/foodserver'],
    '63': ['/nurse'],
} as const;