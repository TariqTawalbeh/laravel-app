. After Installing the porject, you need to run the migration file using php artisan migrate, it will create the database tables for you
. the project has 3 end points in the subscription controller
. in order to access those api's , you should register as a new user or login with your account, using register and login api's 
. the project uses laravel sanctum jwt for registration and login authentication
. the project calls a third party api, it's imaginary so the responses are always not found! (check the Partners helper)
. in the DB, I have users and subscriptions tables, subscriptions store the subscribers without repetetion, so if there is any subscriber who want to unsubscribe then his record in subscriptions table will be modified instead of adding a new record
. there is another table for logging any transactions from any user which it is subscriptions_logs
. for the callback api, it should be called from the partner but it needs an access token, so, what should be done is that the partner has his own account on our system and he can login whenever he wants to get the access token and then he can call the callback api
