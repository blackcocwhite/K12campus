<div class="form-group">
    <label for="title" class="col-md-3 control-label">
        认证状态
    </label>
    <div class="col-md-8">
        @if ($is_auth == 1)
        <p class="form-control-static">已认证</p>
        @else
        <p class="form-control-static">未认证</p>
        @endif
    </div>
</div>


