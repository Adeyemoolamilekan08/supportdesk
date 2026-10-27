<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller {
    public function register(Request $request) {
        $data = $request->validate([
            'company_name'=>'required|string|max:150',
            'name'=>'required|string|max:120',
            'email'=>'required|email|max:190|unique:users,email',
            'password'=>'required|string|min:8|confirmed',
        ]);
        $user = DB::transaction(function () use ($data) {
            $company = Company::create([
                'name'=>$data['company_name'],
                'slug'=>Str::slug($data['company_name']).'-'.Str::lower(Str::random(6)),
                'status'=>'active',
                'settings'=>[],
            ]);
            return $company->users()->create([
                'name'=>$data['name'],'email'=>$data['email'],
                'password'=>$data['password'],'role'=>'company_admin','status'=>'active'
            ]);
        });
        return response()->json(['user'=>$user->load('company:id,name,slug'),'token'=>$user->createToken('supportdesk-web')->plainTextToken], 201);
    }

    // public function login(Request $request) {
    //     $data = $request->validate(['email'=>'required|email','password'=>'required|string']);
    //     if (!Auth::attempt($data)) throw ValidationException::withMessages(['email'=>['The supplied credentials are incorrect.']]);
    //     $user = User::where('email',$data['email'])->firstOrFail();
    //     if ($user->status !== 'active' || ($user->company && $user->company->status !== 'active')) {
    //         Auth::logout();
    //         return response()->json(['message'=>'This account is inactive. Contact your administrator.'], 403);
    //     }
    //     return response()->json(['user'=>$user->load('company:id,name,slug'),'token'=>$user->createToken('supportdesk-web')->plainTextToken]);
    // }

    
public function login(Request $request)
{
    $data = $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $user = User::where('email', $data['email'])->first();

    if (!$user || !Hash::check($data['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The supplied credentials are incorrect.'],
        ]);
    }

    if (
        $user->status !== 'active' ||
        ($user->company && $user->company->status !== 'active')
    ) {
        return response()->json([
            'message' => 'This account is inactive. Contact your administrator.',
        ], 403);
    }

    $token = $user->createToken('supportdesk-web')->plainTextToken;

    return response()->json([
        'user' => $user->load('company:id,name,slug'),
        'token' => $token,
    ]);
}



    public function me(Request $request) { return response()->json(['user'=>$request->user()->load('company:id,name,slug')]); }
    public function logout(Request $request) { $request->user()->currentAccessToken()?->delete(); return response()->json(['message'=>'Logged out successfully.']); }
}
