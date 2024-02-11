<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {







        /*  if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException)
          {
              return response()->json(['status' => false, 'code' => 404, 'message' => 'العنصر الذي تحاول الوصول له لم يعد موجود']);

             // return redirect()->back()->with('custom_modal', ['Model Not Found Exception', $e->getMessage()]);
          }
  */




        if ($request->wantsJson()) {

            return parent::render($request, $e);
        }
        // clock($_GET, $_POST);
        return parent::render($request, $e);
        $class = get_class_name($e);
        if (!method_exists($this, $class)) {
            return call_user_func([$this, 'generalException'], $e);
        }
        return call_user_func([$this, $class], $e);
    }

    protected function generalException($e)
    {


        $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
        $message = $e->getMessage();
        dd($message);
        if (!$message && $statusCode == 404) {
            $message = "URI not found";
        }
        $details = method_exists($e, 'getDetails') ? $e->getDetails() : [];
        $payload = [

            'error' => [
                'status' => $statusCode,
                'name' => get_class_name($e),
                'description' => $message,
                'details' => $details
            ]
        ];

        return $this->jsonResponse($payload);
    }

    /**
     * @SWG\Definition(
     *   definition="ValidationError",
     *   type="object",
     *   @SWG\Property(
     *     property="error",
     *     type="object",
     *     @SWG\Property(
     *       property="status",
     *       type="integer",
     *     ),
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *     ),
     *     @SWG\Property(
     *       property="description",
     *       type="string",
     *     ),
     *     @SWG\Property(
     *       property="details",
     *       type="object",
     *       @SWG\Property(
     *         property="field",
     *         type="array",
     *         @SWG\Items(
     *           type="string",
     *           description="error description"
     *         )
     *       )
     *     )
     *   )
     * )
     *
     * @SWG\Definition(
     *   definition="ModelNotFound",
     *   type="object",
     *   @SWG\Property(
     *     property="error",
     *     type="object",
     *     @SWG\Property(
     *       property="status",
     *       type="integer",
     *       example=500,
     *     ),
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       example="ModelNotFoundException"
     *     ),
     *     @SWG\Property(
     *       property="description",
     *       type="string",
     *       example="No query results for model"
     *     ),
     *     @SWG\Property(
     *       property="details",
     *       type="array",
     *       @SWG\Items(
     *         type="string",
     *         description="error description"
     *       )
     *     )
     *   )
     * )
     */
    protected function ValidationException($e)
    {



        if ($e->getResponse()) {
            $payload = [
                'error' => [
                    'status' => $e->getResponse()->getStatusCode(),
                    'name' => get_class_name($e),
                    'description' => $e->getMessage(),
                    'details' => array_flatten($e->validator->errors()->all())
                ]
            ];
        } else {
            // exception throw manually
            $payload = [
                'error' => [
                    'status' => 400,
                    'name' => get_class_name($e),
                    'description' => $e->getMessage(),
                    'details' => array_flatten($e->validator->errors()->all())
                ]
            ];

        }

        return $this->jsonResponse($payload);
    }

    protected function jsonResponse($payload)
    {
        return response()->json(
            $payload,
            $payload['error']['status']);
    }


    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->ajax() || $request->expectsJson()) {
            return mainResponse(false, 'api.unauthenticated', [], 401,'','');
        }
        if (in_array('admin', explode('/', request()->url()))) {
            return redirect('/admin/login');
        }elseif (in_array('subadmin', explode('/', request()->url()))) {
            return redirect('subadmin/login');
        }else{
            return redirect('/login');
        }
        return mainResponse(false, 'api.unauthenticated', [], 401,'','');

        $guards = array_get($exception->guards() ,0);

        switch ($guards) {
            case 'admin':
                $login = 'admin.login';
                break;

            case 'subadmin':
                $login = 'subadmin.login';
                break;

            default:
                $login = 'login';
                break;
        }



        //return mainResponse(false, 'api.unauthenticated', [], 401,'','');

//        return mainResponse(false, 'api.unauthenticated', [], []);
//        return response()->json(['status' => false ,'message' => __('api.unauthenticated') ]);
    }
}
