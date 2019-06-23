<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFolder extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        //HTML 側での input 要素の name 属性に対応
        return [
            'title' => 'required|max:20',
        ];
    }

    //入力欄の名称をカスタマイズ
    public function attributes()
    {
        return [
            'title' => 'フォルダ名',
        ];
    }
}
