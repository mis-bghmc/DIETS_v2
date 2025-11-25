<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use App\Models\Sanctum\PersonalAccessToken;
use App\Repositories\Doctors\Interfaces\DoctorsOrdersRepositoryInterface;
use App\Repositories\Doctors\DoctorsOrdersRepository;
use App\Services\Doctors\Interfaces\DoctorsOrdersServiceInterface;
use App\Services\Doctors\DoctorsOrdersService;
use App\Repositories\Interfaces\{
    WardsRepositoryInterface,
    PatientsRepositoryInterface,
    NutritionRepositoryInterface,
    NotificationsRepositoryInterface,
    DietTypesRepositoryInterface,
    EmployeesRepositoryInterface,
    UsersRepositoryInterface,
};
use App\Repositories\{
    WardsRepository,
    PatientsRepository,
    NutritionRepository,
    NotificationsRepository,
    DietTypesRepository,
    EmployeesRepository,
    UsersRepository,
};
use App\Services\Interfaces\{
    WardsServiceInterface,
    PatientsServiceInterface,
    NutritionServiceInterface,
    NotificationsServiceInterface,
    DietTypesServiceInterface,
    EmployeesServiceInterface,
    UsersServiceInterface,
};
use App\Services\{
    WardsService,
    PatientsService,
    NutritionService,
    NotificationsService,
    DietTypesService,
    EmployeesService,
    UsersService
};
use App\Repositories\Dietary\{
    MealsRepository, 
    DietsRepository, 
    SNSRepository, 
    FoodRepository, 
    FoodRequestsRepository,
};
use App\Repositories\Dietary\Interfaces\{
    MealsRepositoryInterface, 
    DietsRepositoryInterface, 
    SNSRepositoryInterface, 
    FoodRepositoryInterface, 
    FoodRequestsRepositoryInterface
};
use App\Services\Dietary\{
    MealsService, 
    DietsService, 
    SNSService, 
    FoodService,
    FoodRequestsService,
};
use App\Services\Dietary\Interfaces\{
    MealsServiceInterface, 
    DietsServiceInterface, 
    SNSServiceInterface, 
    FoodServiceInterface,
    FoodRequestsServiceInterface
};


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //  Doctors - Repository
        $this->app->bind(DoctorsOrdersRepositoryInterface::class, DoctorsOrdersRepository::class);

        //  Doctors - Service
        $this->app->bind(DoctorsOrdersServiceInterface::class, DoctorsOrdersService::class);

        //  Repository
        $this->app->bind(WardsRepositoryInterface::class, WardsRepository::class);
        $this->app->bind(PatientsRepositoryInterface::class, PatientsRepository::class);
        $this->app->bind(NutritionRepositoryInterface::class, NutritionRepository::class);
        $this->app->bind(NotificationsRepositoryInterface::class, NotificationsRepository::class);
        $this->app->bind(DietTypesRepositoryInterface::class, DietTypesRepository::class);
        $this->app->bind(EmployeesRepositoryInterface::class, EmployeesRepository::class);
        $this->app->bind(UsersRepositoryInterface::class, UsersRepository::class);

        //  Service
        $this->app->bind(WardsServiceInterface::class, WardsService::class);
        $this->app->bind(PatientsServiceInterface::class, PatientsService::class);
        $this->app->bind(NutritionServiceInterface::class, NutritionService::class);
        $this->app->bind(NotificationsServiceInterface::class, NotificationsService::class);
        $this->app->bind(DietTypesServiceInterface::class, DietTypesService::class);
        $this->app->bind(EmployeesServiceInterface::class, EmployeesService::class);
        $this->app->bind(UsersServiceInterface::class, UsersService::class);

        //  Dietary - Repository
        $this->app->bind(MealsRepositoryInterface::class, MealsRepository::class);
        $this->app->bind(DietsRepositoryInterface::class, DietsRepository::class);
        $this->app->bind(SNSRepositoryInterface::class, SNSRepository::class);
        $this->app->bind(FoodRepositoryInterface::class, FoodRepository::class);
        $this->app->bind(FoodRequestsRepositoryInterface::class, FoodRequestsRepository::class);

        //  Dietary - Service
        $this->app->bind(MealsServiceInterface::class, MealsService::class);
        $this->app->bind(DietsServiceInterface::class, DietsService::class);
        $this->app->bind(SNSServiceInterface::class, SNSService::class);
        $this->app->bind(FoodServiceInterface::class, FoodService::class);
        $this->app->bind(FoodRequestsServiceInterface::class, FoodRequestsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }
}
