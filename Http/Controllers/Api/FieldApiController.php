<?php

namespace Modules\Profile\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Ihelpers\Http\Controllers\Api\BaseApiController;
use Modules\Iprofile\Transformers\FieldTransformer;
use Modules\Profile\Http\Requests\CreateCustomFieldRequest;
use Modules\Profile\Http\Requests\UpdateCustomFieldRequest;
use Modules\Profile\Repositories\FieldRepository;

class FieldApiController extends BaseApiController
{
  private $field;

  public function __construct(FieldRepository $field)
  {
    $this->field = $field;
  }

  /**
   * GET ITEMS
   *
   * @return mixed
   */
  public function index(Request $request)
  {
    try {
      //Get Parameters from URL.
      $params = $this->getParamsRequest($request);

      //Request to Repository
      $fields = $this->field->getItemsBy($params);

      //Response
      $response = [
        "data" => FieldTransformer::collection($fields)
      ];

      //If request pagination add meta-page
      $params->page ? $response["meta"] = ["page" => $this->pageTransformer($fields)] : false;
    } catch (\Exception $e) {
      $status = $this->getStatusError($e->getCode());
      $response = ["errors" => $e->getMessage()];
    }

    //Return response
    return response()->json($response, $status ?? 200);
  }

  /**
   * GET A ITEM
   *
   * @param $criteria
   * @return mixed
   */
  public function show($criteria, Request $request)
  {
    try {
      //Get Parameters from URL.
      $params = $this->getParamsRequest($request);

      //Request to Repository
      $field = $this->field->getItem($criteria, $params);

      //Break if no found item
      if (!$field) throw new Exception('Item not found', 404);

      //Response
      $response = ["data" => new FieldTransformer($field)];

      //If request pagination add meta-page
      $params->page ? $response["meta"] = ["page" => $this->pageTransformer($field)] : false;
    } catch (\Exception $e) {
      $status = $this->getStatusError($e->getCode());
      $response = ["errors" => $e->getMessage()];
    }

    //Return response
    return response()->json($response, $status ?? 200);
  }

  /**
   * CREATE A ITEM
   *
   * @param Request $request
   * @return mixed
   */
  public function create(Request $request)
  {
    \DB::beginTransaction();
    try {
      //Get data
      $data = $request->input('attributes');

      //Validate Request
      $this->validateRequestApi(new CreateCustomFieldRequest($data));

      //Create item
      $this->field->create($data);

      //Response
      $response = ["data" => ""];
      \DB::commit(); //Commit to Data Base
    } catch (\Exception $e) {
      \DB::rollback();//Rollback to Data Base
      $status = $this->getStatusError($e->getCode());
      $response = ["errors" => $e->getMessage()];
    }
    //Return response
    return response()->json($response, $status ?? 200);
  }

  /**
   * UPDATE ITEM
   *
   * @param $criteria
   * @param Request $request
   * @return mixed
   */
  public function update($criteria, Request $request)
  {
    \DB::beginTransaction(); //DB Transaction
    try {
      //Get data
      $data = $request->input('attributes');

      //Validate Request
      $this->validateRequestApi(new UpdateCustomFieldRequest($data));

      //Get Parameters from URL.
      $params = $this->getParamsRequest($request);

      //Request to Repository
      $this->field->updateBy($criteria, $data, $params);

      //Response
      $response = ["data" => ''];
      \DB::commit();//Commit to DataBase
    } catch (\Exception $e) {
      \DB::rollback();//Rollback to Data Base
      $status = $this->getStatusError($e->getCode());
      $response = ["errors" => $e->getMessage()];
    }

    //Return response
    return response()->json($response, $status ?? 200);
  }

  /**
   * DELETE A ITEM
   *
   * @param $criteria
   * @return mixed
   */
  public function delete($criteria, Request $request)
  {
    \DB::beginTransaction();
    try {
      //Get params
      $params = $this->getParamsRequest($request);

      //call Method delete
      $this->field->deleteBy($criteria, $params);

      //Response
      $response = ["data" => ""];
      \DB::commit();//Commit to Data Base
    } catch (\Exception $e) {
      \DB::rollback();//Rollback to Data Base
      $status = $this->getStatusError($e->getCode());
      $response = ["errors" => $e->getMessage()];
    }

    //Return response
    return response()->json($response, $status ?? 200);
  }
}