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

In my AirAsia Gift Card project, each PHP page handles a specific action, such as `card-list.php` or `card-add.php`. The pages use shared files for navigation, validation, and the PDO connection to SQLite. For example, `card-list.php` directly queries the `GIFTCARD` table and also produces the HTML page. Laravel separates the same kinds of responsibilities into routes, controllers, Eloquent models, and Blade views. One benefit is that this separation can make a larger application easier to follow, maintain, and test. We are not converting the AirAsia project now because our current PHP/PDO architecture already supports the course requirements, and rebuilding it in a framework would take time away from completing and testing the assigned functionality. This lab is meant to introduce MVC, not replace the Lab 7 approach.

## Part 3: Evidence of completion

### 1. Screenshot of the running Laravel application

**Action required:** After running the application in Codespaces, save a real screenshot of the page (ideally a saved request or the request list) as `screenshots/laravel-running.png` in your repository. Then replace the line below with:

`![Peer Tutoring Hub running in Codespaces](screenshots/laravel-running.png)`

**Screenshot status:** Not yet attached. A running application has **not** been independently verified by this draft.

### 2. A file from each MVC area

| MVC area | File inspected | What it does |
| --- | --- | --- |
| Route | `app/routes/web.php` | Connects URLs and HTTP methods such as `POST /requests` to controller actions. |
| Controller | `app/app/Http/Controllers/TutoringRequestController.php` | Validates the form, coordinates saving a request, and selects which view to display. |
| Model | `app/app/Models/TutoringRequest.php` | Represents saved tutoring requests and defines the fields and type conversions used by Eloquent. |
| View | `app/resources/views/requests/create.blade.php` | Displays the tutoring-request form and validation feedback to the user. |

*Paths in this table start at the repository root; within the Laravel `app/` directory, remove the first `app/` segment.*

### 3. Reflection

Laravel helped me see how a web application can separate different responsibilities instead of putting most of the work into individual PHP pages. The routes identify what action a URL should trigger, the controller handles the request, the model works with stored data, and Blade templates handle what the user sees. I especially liked how built-in validation and Eloquent reduce repetitive code. The parts that felt newer were Artisan commands, the framework's folder structure, named routes, and route model binding. It takes some time to learn where each file belongs, but I can see why these conventions would help when working on a larger application.

---

## Source files reviewed

- [Starter README](https://github.com/chinmaythorat92/peer-tutoring-laravel-demo/blob/main/README.md)
- [Laravel routes](https://github.com/chinmaythorat92/peer-tutoring-laravel-demo/blob/main/app/routes/web.php)
- [TutoringRequestController](https://github.com/chinmaythorat92/peer-tutoring-laravel-demo/blob/main/app/app/Http/Controllers/TutoringRequestController.php)
- [TutoringRequest model](https://github.com/chinmaythorat92/peer-tutoring-laravel-demo/blob/main/app/app/Models/TutoringRequest.php)
- [Request form Blade template](https://github.com/chinmaythorat92/peer-tutoring-laravel-demo/blob/main/app/resources/views/requests/create.blade.php)
- [Request details Blade template](https://github.com/chinmaythorat92/peer-tutoring-laravel-demo/blob/main/app/resources/views/requests/show.blade.php)
- [AirAsia course project](https://github.com/cschlawin/air-asia)

**Before submitting:** Replace any placeholders, mark only completed steps, insert your actual screenshot, and revise the reflection so it accurately represents your own experience after working in Codespaces.
