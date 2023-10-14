layui.use(['jquery', 'table', 'arronUtil'], function () {
    let $ = layui.jquery,
        arronUtil = layui.arronUtil,
        table = layui.table;

    let controller = {
        listener: function () {

            table.init('currentTableFilter', {
                url: arronUtil.url("/CallType/getCallTypeList"),
                id: 'CallTypeTable',
                toolbar: '#toolbarDemo',
                defaultToolbar: ['filter', 'exports', 'print', {
                    title: '提示',
                    layEvent: 'LAYTABLE_TIPS',
                    icon: 'layui-icon-tips'
                }],
                page: {
                    limits: [15, 30, 45, 60],
                    limit: 15,
                },
                skin: 'line',
                even: true,
            })

            $('#editModal').on('show.bs.modal', e => {
                let id = $(e.relatedTarget).data('id')
                let f = document.editForm, title = ''

                if (id) {
                    title = '编辑'
                } else {
                    title = '新增'
                    f.reset()
                }

                $('#editModel .modal-title').text(title)
            })
            $('form[name=editForm]').submit(function () {
                let formData = $(this).serializeArray(),
                    id = $('[name="id"]').val(),
                    url = id ? arronUtil.url("/CallType/edit") : arronUtil.url("/CallType/add");
                console.log(formData)

                $.post(url, formData, function (res) {
                    let option = { title: res.msg }
                    if (res.code === 1) {
                        option.icon = 'success'
                        option.timer = 2000
                        option.didDestroy = function () {
                            if (id) {
                                table.reload('CallTypeTable', {
                                    page: {
                                        curr: 1
                                    }
                                })
                            } else {
                                table.reload('CallTypeTable')
                            }

                            // 隐藏 modal
                            $('[data-bs-dismiss="modal"]').click()
                        }
                    }

                    arronUtil.Toast.fire(option)
                })

                return false
            })

            // 单元格编辑
            table.on('edit(currentTableFilter)', function (obj) {
                if (!obj.data.name || !obj.data.title) {
                    arronUtil.Toast.fire({ title: '值不能为空' })
                    return false;
                }

                $.post(arronUtil.url("/CallType/edit"), obj.data, function (res) {
                    let option = { title: res.msg }
                    if (res.code === 1) {
                        option.icon = 'success'
                    }

                    arronUtil.Toast.fire(option)
                })
            })
        },
    };

    controller.listener()
});
