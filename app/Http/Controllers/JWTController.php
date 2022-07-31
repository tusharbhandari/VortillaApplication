<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Country;
use App\Models\Login;
use App\Models\Person;
use App\Models\State;
use Auth;
use Validator;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class JWTController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Register user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:100',
            'email' => 'required|string|email|max:100|unique:logins',
            'password' => 'required|string|confirmed|min:6',
            'addresses.name' => 'required|min:3|string|max:100',
            'addresses.street1' => 'required|min:3|string',
            'addresses.street2' => 'min:3|string',
            'addresses.street3' => 'min:3|string',
            'addresses.postal' => 'max:6|integer',
            'addresses.city' => 'string',
            'addresses.state' => 'string',
            'addresses.country' => 'string',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $person = Person::create([
                'name' => $request->name
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
        }
        try {
            $login = Login::create([
                'people_id' => $person,
                'email' => $request->email,
                'pass' => Hash::make($request->password)
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
        }
        
        try {
            $country = Country::firstOrCreate(
                ['name' => $request->address['country']],
            );
            $state = State::firstOrCreate(
                ['name' => $request->address['state']],
                ['countries_id' => $country], 
            );
            $address = Address::create([
                'name' => $request->address['name'],
                'street1' => $request->address['street1'],
                'street2' => $request->address['street2'],
                'street3' => $request->address['street3'],
                'city' => $request->address['city'],
                'postcode' => $request->address['postcode'],
                'state_id' => $state->id,
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
        }

        return response()->json([
            'message' => 'User successfully registered'
        ], 201);
    }

    /**
     * login user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Invalid Credential'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Logout user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'User successfully logged out.']);
    }

    /**
     * Refresh token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get user profile.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        return response()->json(auth()->user());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}