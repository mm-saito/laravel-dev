@php
    $title = __('有休管理');
@endphp

@extends('layouts.template')


@section('title','public_user_index')
@section('description','ディスクリプション')
@section('page_style')

@stop
@include('head')

@section('content')
@include('header')
<div id="app" class="container">
  <h4>残日数：{{$count}}日</h4>
  <h4>申請中：{{$app_count}}件</h4>
  <h3>
    <a class="btn btn-primary" href="{{ url('holidays/app') }}">有給申請</a>
  </h3>
  <table class="table table-hover table-sm">
    <tr class="table-success">
      <th>付与日付</th>
      <th>有効期限</th>
      <th>使用日</th>
      <th>コメント</th>
      <th>状態</th>
      <th></th>
    </tr>
    @foreach($holidays as $holiday)
    <tr>
      <td>{{$holiday->grant_date}}</td>
      <td>{{$holiday->expire_date}}</td>
      <td>{{$holiday->use_date}}</td>
      <td>{{$holiday->comment}}</td>
      <td>{{$holiday->status_name}}</td>
      @if($holiday->status === 1)
        <td>
          <form action="{{ url('holidays/'.$holiday->id.'/cancel') }}" method="POST" name="cancel_holiday">
            @csrf
            <!-- CANCELボタン -->
            <a href="javascript:void(0)" class="text-danger" data-toggle="modal" data-target="#delete_modal_{{$holiday->id}}" data-whatever="@president">
              <i class="fui fui-cross-circle"></i>
            </a>
            <!-- モーダル -->
            <div class="modal fade" id="delete_modal_{{$holiday->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">取り消し確認</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">{{ $holiday->use_date }}を取り消しますか？</div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">CLOSE</button>
                    <button type="submit" class="btn btn-primary">OK</button>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </td>
      @else
        <td></td>
      @endif
    </tr>
    @endforeach
  </table>
</div>
@section('page_script')
@stop
@include('script')
@endsection