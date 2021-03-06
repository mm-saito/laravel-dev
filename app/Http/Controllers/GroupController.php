<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Group;
use App\GroupUser;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $groups = Auth::user()->group()->paginate(10);
        return view('groups.index', compact('groups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();
        $users = User::getArraySelectBox('App\Profile');
        // 自分を選択肢から削除する
        unset($users[$user->id]);
        return view('groups.create', compact('users', 'user'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // すでにチャットをしている相手の場合チャット画面へ
        $chatted_group_id = Group::haveChatted($request->user_id, $request->partner_user_id);
        if ($chatted_group_id > 0) {
            return redirect()->route('chat.show', ['group_id' => $chatted_group_id]);
        }
        DB::beginTransaction();
        try{

            $group = new Group;
            $name = $request->user_id. ' and '. $request->partner_user_id;
            $group->name = $name;
            $group->save();

            // 中間テーブルに保存
            // 自分を登録
            $user_group = new GroupUser;
            $user_group->user_id = $request->user_id;
            $user_group->group_id = $group->id;
            $user_group->save();
            // 相手を登録
            $user_group = new GroupUser;
            $user_group->user_id = $request->partner_user_id;
            $user_group->group_id = $group->id;
            $user_group->save();

        }catch(Exception $e){
            DB::rollback();
            return back()->withInput();
        }
        DB::commit();

        return redirect()->route('chat.show', ['group_id' => $group->id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $group = Group::find($id);
        $group->delete();
        return redirect()->route('group.index');
    }
}
