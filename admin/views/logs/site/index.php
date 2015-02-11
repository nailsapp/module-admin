<div class="group-logs browse sitelog">
    <p>
        Browse recent log files. These log files contain error messages and
        notifications which might be useful for debugging, however they are
        not nessecarily complete as the actual extent of logging can vary.
    </p>
    <p class="system-alert message" id="pleaseNote">
        <strong>Please note:</strong>
        It may take a while to fetch log files on busy, or old, sites.
    </p>
    <hr />
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th class="date">Date</th>
                    <th class="lines">Entries</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody id="logEntries">
                <tr>
                    <td class="no-data" colspan="3">
                        <b class="fa fa-spinner fa-spin"></b>
                        Loading
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<script type="text/template" id="templateNoLogFiles">
    <tr>
        <td colspan="3" class="no-data">
            No log files were found
        </td>
    </td>
</script>
<script type="text/template" id="templateLogRow">
    <tr>
        <td class="date">
            {{date}}
        </td>
        <td class="lines">
            {{lines}}
        </td>
        <td class="actions">
            <a href="<?=site_url('admin/admin/logs/site/view/{{file}}')?>" class="awesome small green">
                View
            </a>
        </td>
    </td>
</script>