<div id="jira-modal" class="modal">
    <div class="modal-background"></div>
    <div class="modal-card">
        <header class="modal-card-head">
            <p class="modal-card-title">Jira</p>
            <button id="jira-btn-modal-close" class="delete" aria-label="close"></button>
        </header>
        <section class="modal-card-body">
            <div class="table-container">
                <table class="table is-stripped is-hoverable is-full-width">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Résumé</th>
                        <th>Estimation</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($issues as $issue): ?>
                        <?php $checked = $current === (int)$issue['id'] ? 'checked' : null ?>
                        <tr>
                            <td><?= $issue['key'] ?></td>
                            <td>
                                <label class="radio">
                                    <input type="radio" name="current" value="<?= $issue['id'] ?>" <?= $checked ?>>
                                    <?= $issue['fields']['summary'] ?>
                                </label>
                            </td>
                            <td><?= $issue['fields']['timetracking']['originalEstimate'] ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </section>
        <footer class="modal-card-foot">
            <button id="jira-btn-update" class="button is-success">
                <span class="icon"><i class="fas fa-play"></i></span>
                <span>Commencer</span>
            </button>
            <button id="jira-btn-delete" class="button is-danger">
                <span class="icon"><i class="fas fa-stop"></i></span>
                <span>Stop</span>
            </button>
            <button id="jira-btn-cancel" class="button">Annuler</button>
        </footer>
    </div>
</div>

<script>
    const btnOpen = document.getElementById('jira-btn-modal-open');
    const btnUpdate = document.getElementById('jira-btn-update');
    const btnDelete = document.getElementById('jira-btn-delete');
    const btnCancel = document.getElementById('jira-btn-cancel');
    const btnClose = document.getElementById('jira-btn-modal-close');
    const modal = document.getElementById('jira-modal');
    const currentA = document.getElementById('jira-current-a');
    const currentText = document.getElementById('jira-current-text');

    btnOpen.addEventListener('click', function (e) {
        modal.classList.add('is-active');
    });

    btnClose.addEventListener('click', function (e) {
        modal.classList.remove('is-active');
    });

    btnCancel.addEventListener('click', function (e) {
        modal.classList.remove('is-active');
    });

    btnUpdate.addEventListener('click', function (e) {
        const issueSelected = document.querySelector('input[name="current"]:checked');
        if (!issueSelected) {
            console.error('No issue selected');
            return;
        }

        btnUpdate.classList.add('is-loading');
        axios.post('/jira/start', {
                current: issueSelected.value
            },
            {withCredentials: true})
            .then(function (response) {
                currentA.href = response.data.a;
                currentText.innerText = response.data.text;
            })
            .catch(function (error) {
                console.log(error);
            });

        modal.classList.remove('is-active');
        btnUpdate.classList.remove('is-loading');
    });

    btnDelete.addEventListener('click', function (e) {
        btnDelete.classList.add('is-loading');
        axios.delete('/jira/stop', null, {withCredentials: true})
            .then(function (response) {
                currentA.href = response.data.a;
                currentText.innerText = response.data.text;
            })
            .catch(function (error) {
                console.log(error);
            });

        modal.classList.remove('is-active');
        btnDelete.classList.remove('is-loading');
    });


</script>
