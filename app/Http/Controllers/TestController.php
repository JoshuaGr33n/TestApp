<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\DataGrid\exampleGrid;
use Illuminate\Http\Request;
use App\Models\Astract_Users;
use App\Models\AppTest;
use App\Models\Astract_Messages;
use App\Models\Astract_Task_Categories;
use App\Models\Astract_Tasks;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class TestController extends Controller
{
    public function index()
    {
        if (Auth::check()) {
            return Redirect::to('logout');
        }
        return view('login');
    }

    public function register()
    {
        if (Auth::check()) {
            return Redirect::to('logout');
        }
        return view('register');
    }

    public function store(Request $request)
    {

        $messages = [
            'name.required' => 'First name is required',
            'phone.required' => 'Phone is required',
            'phone.unique' => 'Phone number already taken',
            'phone.min' => 'Phone must be 11 digits',
            'phone.max' => 'Phone must be 11 digits',
            'email.required' => 'Email is required',
            'email.email' => 'Invalid email',
            'email.required_with' => 'Email doesnt match',
            'email.unique' => 'Email already taken',
            'repeat_email.required' => 'Required',
            'repeat_email.same' => 'Email doesnt match',
            'password.min' => 'Password must be atleast 6 digits',
            'password.required' => 'Required',
            'repeat_password.required' => 'Required',
            'repeat_password.same' => 'Password doesnt match',
            'agree_term.required' => 'You must agree to the terms of this site',
        ];
        $request->validate([
            'name' => 'required',
            'phone' => 'required|unique:astract__users|min:11|max:11',
            'email' => 'required|email|required_with|unique:astract__users',
            'repeat_email' => 'required|same:email',
            'password' => 'required|min:6|',
            'repeat_password' => 'required|same:password',
            'agree_term' => 'required',
        ], $messages);

        $newUser = new Astract_Users;
        $newUser->name = $request->name;
        $newUser->phone = $request->phone;
        $newUser->email = $request->email;
        $newUser->password = Hash::make($request->password);
        $newUser->save();

        return response()->json(['success' => 'Done!']);
    }

    public function login(Request $request)
    {

        $rules = array(
            'email'    => 'required|email',
            'password' => 'required|'
        );

        $messages = [

            'email.required' => 'Email is required',
            'email.email' => 'Invalid email',
            'password.required' => 'Required',

        ];


        $validator = Validator::make($request->all(), $rules, $messages);


        if ($validator->fails()) {
            return Redirect::to('/')
                ->withErrors($validator)
                ->withRequest($request->except('password'));
        } else {

            $userdata = array(
                'email'     => $request->get('email'),
                'password'  => $request->get('password')
            );

            // attempt login
            if (Auth::attempt($userdata)) {
                if (Auth::user()->admin == true) {
                    return response()->json(['feedback' => 'Admin']);
                } else {
                    if (Auth::user()->status == 1) {
                        $user = Auth::user();
                        return response()->json(['feedback' => 1]);
                    } else {
                        Auth::logout();
                        return response()->json(['feedback' => 2]);
                    }
                }
            } else {
                return response()->json(['feedback' => 0]);
            }
        }
    }


    public function logout()
    {
        Auth::logout();
        return Redirect::to('/');
    }

    public function user_page()
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == true) {
            return Redirect::to('logout');
        }

        $messages = Astract_Messages::get();

        //update overdue deadlines
        $all_tasks = Astract_Tasks::select('*')
            ->get();
        $todaysDate = new Carbon;
        foreach ($all_tasks as $task) {
            $updateTask = Astract_Tasks::find($task->id);
            if ($todaysDate >= $task->deadline) {

                if ($task->status == 1) {
                    $updateTask->status = 0;
                } elseif ($task->status == 2) {
                    $updateTask->status = 2;
                }
                $updateTask->save();
            } else {

                if ($task->status == 1) {
                    $updateTask->status = 1;
                } elseif ($task->status == 2) {
                    $updateTask->status = 2;
                } elseif ($task->status == 0) {
                    $updateTask->status = 1;
                }
                $updateTask->save();
            }
        }

        $all_tasks_count = Astract_Tasks::select('*')
            ->where('user_id', '=', Auth::id())
            ->get()->count();

        $completed_tasks_count = Astract_Tasks::select('*')
            ->where([['user_id', '=', Auth::id()], ['status', '=', 2]])
            ->get()->count();

        $pending_tasks_count = Astract_Tasks::select('*')
            ->where([['user_id', '=', Auth::id()], ['status', '=', 1]])
            ->get()->count();

        $overdue_tasks_count = Astract_Tasks::select('*')
            ->where([['user_id', '=', Auth::id()], ['status', '=', 0]])
            ->get()->count();

        $most_recent_tasks = Astract_Tasks::select('*')->where([['user_id', '=', Auth::id()]])->take(5)->orderBy('created_at', 'DESC')->get();
        $most_recent_tasks_count = $most_recent_tasks->count();
        return view('user', [
            'messages' => $messages, 'all_tasks_count' => $all_tasks_count, 'completed_tasks_count' => $completed_tasks_count,
            'pending_tasks_count' => $pending_tasks_count, 'overdue_tasks_count' => $overdue_tasks_count,
            'most_recent_tasks' => $most_recent_tasks, 'most_recent_tasks_count' => $most_recent_tasks_count
        ]);
    }

    public function admin_page()
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == false) {
            return Redirect::to('logout');
        }

        //update overdue deadlines
        $all_tasks = Astract_Tasks::select('*')
            ->get();
        $todaysDate = new Carbon;
        foreach ($all_tasks as $task) {
            $updateTask = Astract_Tasks::find($task->id);
            if ($todaysDate >= $task->deadline) {

                if ($task->status == 1) {
                    $updateTask->status = 0;
                } elseif ($task->status == 2) {
                    $updateTask->status = 2;
                }
                $updateTask->save();
            } else {

                if ($task->status == 1) {
                    $updateTask->status = 1;
                } elseif ($task->status == 2) {
                    $updateTask->status = 2;
                } elseif ($task->status == 0) {
                    $updateTask->status = 1;
                }
                $updateTask->save();
            }
        }

        $all_tasks_count = Astract_Tasks::select('*')
            ->get()->count();

        $completed_tasks_count = Astract_Tasks::select('*')
            ->where([['status', '=', 2]])
            ->get()->count();

        $pending_tasks_count = Astract_Tasks::select('*')
            ->where([['status', '=', 1]])
            ->get()->count();

        $overdue_tasks_count = Astract_Tasks::select('*')
            ->where([['status', '=', 0]])
            ->get()->count();

        $most_recent_tasks = Astract_Tasks::select('*')->take(5)->orderBy('created_at', 'DESC')->get();
        $most_recent_tasks_count = $most_recent_tasks->count();

        $most_recent_users = Astract_Users::select('*')->where([['admin', '=', false]])->take(5)->orderBy('created_at', 'DESC')->get();
        $most_recent_users_count = $most_recent_users->count();
        $all_users_count = Astract_Users::select('*')->where([['admin', '=', false]])->get()->count();

        return view('admin', [
            'all_tasks_count' => $all_tasks_count, 'completed_tasks_count' => $completed_tasks_count,
            'pending_tasks_count' => $pending_tasks_count, 'overdue_tasks_count' => $overdue_tasks_count,
            'most_recent_tasks' => $most_recent_tasks, 'most_recent_tasks_count' => $most_recent_tasks_count,
            'most_recent_users' => $most_recent_users, 'most_recent_users_count' => $most_recent_users_count,
            'all_users_count' => $all_users_count
        ]);
    }
    // for laravel
    public function allUsers()
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == false) {
            return Redirect::to('logout');
        }

        $users = Astract_Users::select('*')
            ->where('admin', '=', false)
            ->get();
        return view('all-users', ['users' => $users]);
    }

    //for VUE
    public function getUsers()
    {

        $users = Astract_Users::select('*')
            ->where('admin', '=', false)
            ->get();
        return $users;
    }

    public function update_user_status(Request $request, $id)
    {
        $user = Astract_Users::find($id);
        if ($user) {
            if ($user->status == 1) {
                $user->status = 0;
                $label = "Pending";
            } else {
                $user->status = 1;
                $label = "Active";
            }
            if ($user->save()) {

                return response()->json(['res' => $id, 'label' => $label]);
            }
        }
        return "Item Not Found.";
    }

    public function admin_inbox()
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == false) {
            return Redirect::to('logout');
        }

        $messages = Astract_Messages::select('*')->where('recepient', '=', "Admin")->orderBy('created_at', 'desc')->get();
        $count_unread_messages = Astract_Messages::select('*')->where([['recepient', '=', "Admin"], ['status', '=', false]])->count();
        if ($count_unread_messages > 0) {
            $count_unread = '(' . $count_unread_messages . ')';
        } else {
            $count_unread = "";
        }
        return view('admin-inbox', ['messages' => $messages, 'count_unread' => $count_unread]);
    }

    public function admin_outbox()
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == false) {
            return Redirect::to('logout');
        }
        $messages = Astract_Messages::select('*')->where('sender', '=', 'Admin')->orderBy('created_at', 'desc')->get();

        return view('admin-outbox', ['messages' => $messages]);
    }

    public function user_inbox()
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == true) {
            return Redirect::to('logout');
        }
        $messages = Astract_Messages::select('*')->where('recepient', '=', Auth::user()->email)->orderBy('created_at', 'desc')->get();
        $count_unread_messages = Astract_Messages::select('*')->where([['recepient', '=', Auth::user()->email], ['status', '=', false]])->count();
        if ($count_unread_messages > 0) {
            $count_unread = '(' . $count_unread_messages . ')';
        } else {
            $count_unread = "";
        }
        return view('user-inbox', ['messages' => $messages, 'count_unread' => $count_unread]);
    }

    public function user_outbox()
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == true) {
            return Redirect::to('logout');
        }
        $messages = Astract_Messages::select('*')->where('sender', '=', Auth::user()->email)->orderBy('created_at', 'desc')->get();

        return view('user-outbox', ['messages' => $messages]);
    }

    public function admin_view_outbox_message($id)
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == false) {
            return Redirect::to('logout');
        }
        $message = Astract_Messages::find($id);
        $tag = "outbox";

        return view('view-message', ['message' => $message, 'tag' => $tag]);
    }

    public function admin_view_inbox_message($id)
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == false) {
            return Redirect::to('logout');
        }
        $message = Astract_Messages::find($id);
        $message->status = true;
        $message->save();
        $tag = "inbox";

        return view('view-message', ['message' => $message, 'tag' => $tag]);
    }

    public function user_view_outbox_message($id)
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == true) {
            return Redirect::to('logout');
        }
        $message = Astract_Messages::find($id);
        $tag = "outbox";

        return view('view-message', ['message' => $message, 'tag' => $tag]);
    }

    public function user_view_inbox_message($id)
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == true) {
            return Redirect::to('logout');
        }
        $message = Astract_Messages::find($id);
        $message->status = true;
        $message->save();
        $tag = "inbox";

        return view('view-message', ['message' => $message, 'tag' => $tag]);
    }

    public function getMessages()
    {
        $messages = Astract_Messages::get();

        return $messages;
    }

    public function send_message(Request $request)
    {


        if (Auth::user()->admin == true) {

            $messages = [
                'title.required' => 'Required',
                'recepient.required' => 'Required',
                'message.required' => 'Required',

            ];
            $request->validate([
                'title' => 'required',
                'recepient' => 'required',
                'message' => 'required',
            ], $messages);

            $sender = "Admin";
            $recepient = $request->recepient;
        } else {
            $messages = [
                'title.required' => 'Required',
                'message.required' => 'Required',

            ];
            $request->validate([
                'title' => 'required',
                'message' => 'required',
            ], $messages);

            $sender = Auth::user()->email;
            $recepient = "Admin";
        }
        $newMessage = new Astract_Messages;
        $newMessage->title = $request->title;
        $newMessage->sender = $sender;
        $newMessage->recepient = $recepient;
        $newMessage->message = $request->message;
        $newMessage->save();
        return response()->json(['success' => 'Sent']);
    }

    public function update_message_status(Request $request, $id)
    {
        $message = Astract_Messages::find($id);
        if ($message) {
            if ($message->status == true) {
                $message->status = false;
                $label = "Unread";
            } else {
                $message->status = true;
                $label = "Read";
            }
            if ($message->save()) {

                return response()->json(['res' => $id, 'label' => $label]);
            }
        }
        return "Item Not Found.";
    }

    public function deleteUser($id)
    {
        $user = Astract_Users::find($id);
        if ($user) {
            $user->delete();
            return response()->json(['res' => 1]);
        }
        return "Item Not Found.";
    }

    public function deleteMessage($id)
    {
        $message = Astract_Messages::find($id);
        if ($message) {
            $message->delete();
            return response()->json(['res' => 1]);
        }
        return "Item Not Found.";
    }

    public function task_categories()
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == false) {
            return Redirect::to('logout');
        }

        $categories = Astract_Task_Categories::orderBy('created_at', 'DESC')->get();

        return view('task-categories', ['categories' => $categories]);
    }

    public function create_category(Request $request)
    {

        $messages = [
            'title.required' => 'Required',
            'title.unique' => 'Title Already Exist. Type in a unique title',
        ];
        $request->validate([
            'title' => 'required|unique:astract__task__categories',
        ], $messages);

        if ($request->status) {
            $status = true;
        } else {
            $status = false;
        }

        $newCategory = new Astract_Task_Categories;
        $newCategory->title = ucfirst($request->title);
        $newCategory->status = $status;
        $newCategory->save();
        return response()->json(['success' => 'Sent']);
    }

    public function update_category_status(Request $request, $id)
    {
        $category = Astract_Task_Categories::find($id);
        if ($category) {
            if ($category->status == true) {
                $category->status = false;
                $label = "Deactivated";
            } else {
                $category->status = true;
                $label = "Active";
            }
            if ($category->save()) {

                return response()->json(['res' => $id, 'label' => $label]);
            }
        }
        return "Item Not Found.";
    }


    public function deleteCategory($id)
    {
        $category = Astract_Task_Categories::find($id);
        $task = Astract_Tasks::select('*')
            ->where('category_id', '=', $id);
        if ($category) {
            $category->delete();
            $task->delete();
            return response()->json(['res' => 1]);
        }
        return "Item Not Found.";
    }

    public function tasks()
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == true) {
            return Redirect::to('logout');
        }

        //update overdue deadlines
        $all_tasks = Astract_Tasks::select('*')
            ->get();
        $todaysDate = new Carbon;
        foreach ($all_tasks as $task) {
            $updateTask = Astract_Tasks::find($task->id);
            if ($todaysDate >= $task->deadline) {

                if ($task->status == 1) {
                    $updateTask->status = 0;
                } elseif ($task->status == 2) {
                    $updateTask->status = 2;
                }
                $updateTask->save();
            } else {

                if ($task->status == 1) {
                    $updateTask->status = 1;
                } elseif ($task->status == 2) {
                    $updateTask->status = 2;
                } elseif ($task->status == 0) {
                    $updateTask->status = 1;
                }
                $updateTask->save();
            }
        }

        $tasks = Astract_Tasks::select('*')
            ->where('user_id', '=', Auth::id())
            ->orderBy('created_at', 'DESC')
            ->get();
        $count_active_categories = Astract_Task_Categories::select('*')
            ->where('status', '=', true)
            ->get()->count();

        return view('tasks', ['tasks' => $tasks, 'count_active_categories' => $count_active_categories]);
    }

    //for VUE
    public function getCategories()
    {
        $categories = Astract_Task_Categories::select('*')
            ->where('status', '=', true)
            ->get();
        return $categories;
    }
    public function getUserTasks()
    {
        $tasks = Astract_Tasks::select('*')
            ->where('user_id', '=', Auth::id())
            ->orderBy('created_at', 'DESC')
            ->get();
        return $tasks;
    }

    public function create_task(Request $request)
    {

        $messages = [
            'title.required' => 'Required',
            'category.required' => 'Required',
            'deadline.required' => 'Required',
            'status.required' => 'Required',

        ];
        $request->validate([
            'title' => 'required',
            'category' => 'required',
            'deadline' => 'required',
            'status' => 'required',
        ], $messages);

        $newTask = new Astract_Tasks;
        $newTask->user_id = Auth::id();
        $newTask->title = $request->title;
        $newTask->category_id = $request->category;
        $newTask->deadline = $request->deadline;
        $newTask->status = $request->status;
        $newTask->save();
        return response()->json(['success' => 'Sent']);
    }

    public static function call_category($category_id)
    {
        $category = Astract_Task_Categories::select('title')
            ->where('id', '=', $category_id)->get();

        foreach ($category as $c) {
            return $c->title;
        }
    }
    public static function check_category_status($category_id)
    {
        $status = Astract_Task_Categories::select('status')
            ->where('id', '=', $category_id)->get();


        foreach ($status as $s) {
            if ($s->status == false) {
                return 'Category Deactivated';
            } else {
                return " ";
            }
        }
    }
    public static function check_category_status_class_tag($category_id)
    {
        $status = Astract_Task_Categories::select('status')
            ->where('id', '=', $category_id)->get();


        foreach ($status as $s) {
            if ($s->status == false) {
                return 'label label-danger';
            } else {
                return " ";
            }
        }
    }
    public static function call_user($user_id)
    {
        $user = Astract_Users::select('name')
            ->where('id', '=', $user_id)->get();

        foreach ($user as $u) {
            return $u->name;
        }
    }

    public static function task($id)
    {
        $task = Astract_Tasks::select('*')
            ->where('id', '=', $id)->get();

        return $task;
    }

    public function update_task(Request $request)
    {
        $todaysDate = new Carbon;
        $updateTask = Astract_Tasks::find($request->Uid);
        if (Auth::user()->admin == false) {
            $messages = [
                'edit_title.required' => 'Required',
                'edit_category.required' => 'Required',
                'edit_deadline.required' => 'Required',
                'edit_status.required' => 'Required',

            ];
            $request->validate([
                'edit_title' => 'required',
                'edit_category' => 'required',
                'edit_deadline' => 'required',
                'edit_status' => 'required',
            ], $messages);

            $user_id = Auth::id();
        } else {
            $messages = [
                'edit_user_id.required' => 'Required',
                'edit_title.required' => 'Required',
                'edit_category.required' => 'Required',
                'edit_deadline.required' => 'Required',
                'edit_status.required' => 'Required',

            ];
            $request->validate([
                'edit_user_id' => 'required',
                'edit_title' => 'required',
                'edit_category' => 'required',
                'edit_deadline' => 'required',
                'edit_status' => 'required',
            ], $messages);
            $user_id = $request->edit_user_id;
        }
        $updateTask->user_id = $user_id;
        $updateTask->title = $request->edit_title;
        $updateTask->category_id = $request->edit_category;
        $updateTask->deadline = $request->edit_deadline;

        if ($todaysDate >= $request->edit_deadline) {
            if ($request->edit_status == 1) {
                $updateTask->status = 0;
            } elseif ($request->edit_status == 2) {
                $updateTask->status = 2;
            }
        } else {

            if ($request->edit_status == 0) {
                $updateTask->status = 1;
            }else{
                $updateTask->status = $request->edit_status;
            }
           
            
        }

        $updateTask->save();

        if ($updateTask->save()) {
            return response()->json(['success' => 'Sent']);
        }

        
    }

    public function delete($id)
    {
        $task = AppTest::find($id);
        if ($task) {
            $task->delete();
            return response()->json(['res' => 1]);
        }
        return "Item Not Found.";
    }


    public function update_taskt(Request $request)
    {
        $updateProduct = AppTest::find(2);
    
            $request->validate([
                'edit_product_name' => 'required',
                'edit_quantity' => 'required',
                'edit_price' => 'required',
            ]);
         
        $updateProduct->product_name = "77";
        $updateProduct->quantity = $request->edit_quantity;
        $updateProduct->price = $request->edit_price;

        $updateProduct->save();

        if ($updateProduct->save()) {
            return response()->json(['success' => 'Sent']);
        }
        // return $id;
    }

    public function admin_tasks_view()
    {
        if (!Auth::check()) {
            return Redirect::to('/');
        }
        if (Auth::user()->admin == false) {
            return Redirect::to('logout');
        }

        //update overdue deadlines
        $all_tasks = Astract_Tasks::select('*')
            ->get();
        $todaysDate = new Carbon;
        foreach ($all_tasks as $task) {
            $updateTask = Astract_Tasks::find($task->id);
            if ($todaysDate >= $task->deadline) {

                if ($task->status == 1) {
                    $updateTask->status = 0;
                } elseif ($task->status == 2) {
                    $updateTask->status = 2;
                }
                $updateTask->save();
            } else {

                if ($task->status == 1) {
                    $updateTask->status = 1;
                } elseif ($task->status == 2) {
                    $updateTask->status = 2;
                } elseif ($task->status == 0) {
                    $updateTask->status = 1;
                }
                $updateTask->save();
            }
        }

        $tasks = Astract_Tasks::select('*')
            ->orderBy('created_at', 'DESC')
            ->get();
        $count_active_categories = Astract_Task_Categories::select('*')
            ->where('status', '=', true)
            ->get()->count();

        return view('admin-tasks-view', ['tasks' => $tasks, 'count_active_categories' => $count_active_categories]);
    }
}
