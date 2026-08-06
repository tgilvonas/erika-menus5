<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;
use Inertia\Response;

class UsersController
{
    public function index(): Response
    {
        return Inertia::render('Users');
    }

    public function getJsonList()
    {
        $query = User::query()->select(['id', 'name', 'email', 'roles']);

        if ($search = request('search_text')) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $allowedFields = ['name', 'email', 'roles'];
        $orderByField = in_array(request('order_by_field'), $allowedFields, true)
            ? request('order_by_field')
            : 'name';

        $orderByDirection = request('order_by_direction') === 'desc' ? 'desc' : 'asc';
        $paginateBy = is_numeric(request('paginate_by')) ? intval(request('paginate_by')) : 10;

        return $query->orderBy($orderByField, $orderByDirection)->paginate($paginateBy);
    }

    public function save(UserRequest $request)
    {
        if (is_numeric($request->id)) {
            $user = User::query()->findOrFail($request->id);
            $msgPart = __('general.updated');
        } else {
            $user = new User();
            $msgPart = __('general.created');
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->roles = $request->roles;

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return [
            'result' => 'success',
            'message' => __('general.user').' "'.$user->name.'" '.$msgPart,
            'user' => $user,
        ];
    }

    public function delete()
    {
        $user = User::query()->findOrFail(request('id'));

        $user->delete();

        return [
            'result' => 'success',
            'message' => __('general.user').' "'.$user->name.'" '.__('general.deleted'),
        ];
    }
}
