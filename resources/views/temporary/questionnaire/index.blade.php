@extends('temporary.layout')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">

                @include('temporary.partials.errors')
                @include('temporary.partials.success')

                <table id="tags-table" class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th>学校名称</th>
                        <th>学校地址</th>
                        <th class="hidden-sm">运营者姓名</th>
                        <th class="hidden-md">运营者身份证</th>
                        <th class="hidden-md">运营者手机</th>
                        <th class="hidden-md">运营者邮箱</th>
                        <th data-sortable="false">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach ($datas as $data)
                        <tr>
                            <td>@if ($data->is_connect)
                                    <p class="btn btn-success btn-sm">已联系</p>
                                    @else <p class="btn btn-warning btn-sm">未联系</p>
                                @endif
                                {{ $data->school_name }}</td>
                            <td>{{ $data->city }}</td>
                            <td class="hidden-md">{{ $data->operator_name }}</td>
                            <td class="hidden-sm">{{ $data->operator_id_number }}</td>
                            <td class="hidden-md">{{ $data->operator_phone }}</td>
                            <td class="hidden-md">{{ $data->operator_email }}</td>
                            {{--<td class="hidden-sm">--}}
                                {{--@if ($data->reverse_direction)--}}
                                    {{--Reverse--}}
                                {{--@else--}}
                                    {{--Normal--}}
                                {{--@endif--}}
                            {{--</td>--}}
                            <td>
                                <a href="/temporary/questionnaire/{{ $data->id }}/show" class="btn btn-xs btn-info">
                                    <i class="fa fa-edit"></i> 详情
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('scripts')
    <script>
        $(function() {
            $("#tags-table").DataTable({
            });
        });
    </script>
@stop