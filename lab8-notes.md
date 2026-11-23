# Lab 8: Laravel MVC Demo — Peer Tutoring Hub

## Part 1: Running the application

**Repository used:** [Peer Tutoring Hub Laravel Demo](https://github.com/chinmaythorat92/peer-tutoring-laravel-demo)

**My Codespaces/repository link:** [Add the link to your own fork or Codespace here]

**Completion checklist (mark only after doing these steps):**
<img width="1897" height="984" alt="image" src="https://github.com/user-attachments/assets/a5ff6fa0-4fd0-4541-9fec-47921850997a" />


## Part 2: Exploring MVC

### 1. What business purpose does the Peer Tutoring Hub application support?

The Peer Tutoring Hub gives students a way to request help with a course. A student selects a class, explains what they need help with, chooses topics, and enters a preferred date and time. The app checks the information and saves the request so it can be viewed again. It helps organize incoming tutoring requests, although this demo does not actually assign tutors or schedule sessions.

### 2. Identify one route used by the application. What URL or action causes that route to run?

One route is `POST /requests`, defined in `routes/web.php`. It runs when someone completes the **Request a tutoring session** form and clicks **Request session**. The route points to the `store` method of `TutoringRequestController`. Another route, `GET /requests`, displays the list of requests.

### 3. Identify the controller involved in one application workflow. What is it responsible for?

`TutoringRequestController.php` handles the tutoring-request workflow. Its `create()` method opens the form, `store()` validates the submitted information and saves it, `index()` loads the request list, and `show()` displays a selected request. The controller connects the incoming request to the database model and the correct view.

### 4. Identify one model. What business data does it represent?

The `TutoringRequest` model in `app/Models/TutoringRequest.php` represents a tutoring request stored in the `tutoring_requests` database table. The data includes the course, reason for seeking help, topics, preferred date, and preferred time. The table also has a request ID, status, and timestamps. Laravel's Eloquent model makes it possible to create and retrieve these records without writing a separate SQL query for every action.

### 5. Identify one Blade view. What does the user see or do on that page?

The `resources/views/requests/create.blade.php` view displays the request form. The student can choose a course, select a preferred date and time, describe the help needed, and check one or more topics. If validation fails, the page shows error messages and keeps previous entries. It uses the shared `layouts/app.blade.php` layout for the header, navigation, and footer.

### 6. Explain how a request moves through MVC for one workflow.

For the **submit a tutoring request** workflow, the form in `requests/create.blade.php` sends a `POST` request to `/requests`. The route in `routes/web.php` calls `TutoringRequestController::store()`. The controller validates the fields, including the allowed course and topics, description length, and preferred date. If the data is valid, the controller calls `TutoringRequest::create()` to save a new record in SQLite through the Eloquent model. Laravel then redirects the browser to the named `requests.show` route at `/requests/{tutoringRequest}`. Route model binding loads the saved request, `show()` sends it to the `requests/show.blade.php` view, and Blade renders the confirmation and saved details. If validation fails, Laravel returns to the form with errors instead of creating a record.

### 7. Compare Laravel's MVC organization with the architecture used in your course project.

A major difference I noticed while exploring Laravel was how URLs and PHP files are handled. I attempted adding /routes/web.php to the end of my Codespaces URL, anticipating that it would open the PHP file like I was used to doing with my Peer Tutoring Hub project. Instead I received a 404 Not Found Error. I learned that Laravel does not allow users to access source files through the browser, instead Laravel uses routes defined in routes/web.php to determine which controller handles a request. 

## Part 3: Evidence of completion

### 1. Screenshot of the running Laravel application
<img width="1087" height="880" alt="image" src="https://github.com/user-attachments/assets/5ad4cf70-670c-4b09-9a8a-f9894e5ad106" />

**Screenshot status:** Not yet attached. A running application has **not** been independently verified by this draft.

### 2. A file from each MVC area

| MVC area | File inspected | What it does |
| --- | --- | --- |
| Route | `app/routes/web.php` | <img width="1008" height="772" alt="image" src="https://github.com/user-attachments/assets/fda09b9b-9092-4881-9c06-200db7661eed" />
 |
| Controller | `app/app/Http/Controllers/TutoringRequestController.php` | <img width="819" height="247" alt="image" src="https://github.com/user-attachments/assets/07767a11-4cad-4448-952c-4858b0f69def" />
 |
| Model | `app/app/Models/TutoringRequest.php` | <img width="925" height="546" alt="image" src="https://github.com/user-attachments/assets/eb0de098-9519-42c3-92b0-af6c2350bdff" />
 |
| View | `app/resources/views/requests/create.blade.php` | <img width="1063" height="760" alt="image" src="https://github.com/user-attachments/assets/2a135a5c-c4b5-48b4-a40c-6dc2dc8b53ec" />
 |


### 3. Reflection

Laravel helped me see how a web application can separate different responsibilities instead of putting most of the work into individual PHP pages. The routes identify what action a URL should trigger, the controller handles the request, the model works with stored data, and Blade templates handle what the user sees. I especially liked how built-in validation and Eloquent reduce repetitive code. The parts that felt newer were Artisan commands, the framework's folder structure, named routes, and route model binding. It takes some time to learn where each file belongs, but I can see why these conventions would help when working on a larger application.

---
