
<script setup>
definePageMeta({
    layout: false,
    middleware: [
        function (from) {
            const { setAuthToken } = useAuth();
            const params = from.query;

            setAuthToken(params.token);
            return navigateTo(params.token ? `/doctors/patient/${params.enccode}` : '/auth/login');
        }
    ],
});
</script>
