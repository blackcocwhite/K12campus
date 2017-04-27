@extends('temporary.layout')

@section('content')
    <div class="container-fluid">
        <div class="row page-title-row">
            <div class="col-md-12">
                <h3>资料审核
                    <small>» 资料详情</small>
                </h3>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">资料详情</h3>
                    </div>
                    <div class="panel-body">

                        @include('temporary.partials.errors')
                        @include('temporary.partials.success')

                        <form class="form-horizontal" role="form" method="POST" action="/temporary/questionnaire/{{ $id }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="id" value="{{ $id }}">

                            <div class="form-group">
                                <label for="tag" class="col-md-3 control-label">学校名称</label>
                                <div class="col-md-3">
                                    <p class="form-control-static">{{ $school_name }}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tag" class="col-md-3 control-label">学校地址</label>
                                <div class="col-md-3">
                                    <p class="form-control-static">{{ $city }}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tag" class="col-md-3 control-label">运营者姓名</label>
                                <div class="col-md-3">
                                    <p class="form-control-static">{{ $operator_name }}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tag" class="col-md-3 control-label">运营者身份证</label>
                                <div class="col-md-3">
                                    <p class="form-control-static">{{ $operator_id_number }}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tag" class="col-md-3 control-label">运营者手机</label>
                                <div class="col-md-3">
                                    <p class="form-control-static">{{ $operator_phone }}</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="tag" class="col-md-3 control-label">运营者邮箱</label>
                                <div class="col-md-3">
                                    <p class="form-control-static">{{ $operator_email }}</p>
                                </div>
                            </div>
                            @if($has_official_account)
                                @include('temporary.questionnaire._auth_form')
                            @else
                                @include('temporary.questionnaire._notauth_form')
                            @endif
                            <div class="form-group">
                                @if($is_connect == 1)
                                <div class="col-md-7 col-md-offset-3">
                                    <input type="hidden" name="is_connect" value="0">
                                    <button type="submit" class="btn btn-primary btn-md">
                                        <i class="fa fa-save"></i>
                                        取消联系
                                    </button>
                                    <button type="button" class="btn btn-danger btn-md" data-toggle="modal"
                                            data-target="#modal-delete">
                                        <i class="fa fa-times-circle"></i>
                                        Delete
                                    </button>

                                </div>
                                    @else
                                    <div class="col-md-7 col-md-offset-3">
                                        <input type="hidden" name="is_connect" value="1">
                                        <button type="submit" class="btn btn-primary btn-md">
                                            <i class="fa fa-save"></i>
                                            确认联系
                                        </button>

                                        <button type="button" class="btn btn-danger btn-md" data-toggle="modal"
                                                data-target="#modal-delete">
                                            <i class="fa fa-times-circle"></i>
                                            Delete
                                        </button>
                                    </div>
                                    @endif
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 确认删除 --}}
    <div class="modal fade" id="modal-delete" tabIndex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        ×
                    </button>
                    <h4 class="modal-title">Please Confirm</h4>
                </div>
                <div class="modal-body">
                    <p class="lead">
                        <i class="fa fa-question-circle fa-lg"></i>
                        Are you sure you want to delete this tag?
                    </p>
                </div>
                <div class="modal-footer">
                    <form method="POST" action="/temporary/questionnaire/{{ $id }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-times-circle"></i> Yes
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@stop