import { updatePreset, updateSurfacePalette } from '@primevue/themes';

const { layoutConfig } = useLayout();
const { load } = useThemeCookie();
const { primaryColors, surfaces, updateColors }  = useAppConfigurator();
const { save } = useThemeCookie();

export default defineNuxtPlugin(() => {
    const theme: any = load('theme') || 'dark';
    const primary: any = load('primary');
    const surface: any = load('surface');
    
    //  Theme
    if(theme){
        const isDark = theme === 'dark'
        layoutConfig.darkTheme = isDark;
        
        if(isDark) {
            useHead({
                htmlAttrs: {
                  class: 'app-dark'
                }
            });
        }

        save('theme', isDark ? 'dark' : 'light');
    }

    //  Primary
    if(primary){
        layoutConfig.primary = primary.name;
        updatePreset(primary.ext);

    }else{
        updateColors('primary', primaryColors.value?.find(item => item.name === 'orange'));
    }

    //  Surface
    if(surface){
        layoutConfig.surface = surface.name;
        updateSurfacePalette(surface.palette);

    }else{
        updateColors('surface', surfaces.value?.find(item => item.name === 'slate'));
    }
});