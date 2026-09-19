<dialog class="mk-confirm-dialog" data-confirm-dialog aria-labelledby="mk-confirm-title" aria-describedby="mk-confirm-message">
    <div class="mk-confirm-card">
        <div class="mk-confirm-heading">
            <span class="mk-confirm-icon" aria-hidden="true"><i class="bi bi-exclamation-triangle"></i></span>
            <div>
                <h2 class="mk-confirm-title" id="mk-confirm-title" data-confirm-dialog-title>Konfirmasi tindakan</h2>
                <p class="mk-confirm-message" id="mk-confirm-message" data-confirm-dialog-message>Pastikan Anda ingin melanjutkan proses ini.</p>
            </div>
        </div>
        <div class="mt-3" data-confirm-password-section hidden>
            <label class="form-label small" for="mk-admin-password">Password admin Anda</label>
            <input id="mk-admin-password" type="password" class="form-control" autocomplete="current-password" maxlength="128" data-confirm-password-input>
            <p class="small text-muted mt-2 mb-0">Verifikasi diperlukan untuk mereset password pengguna.</p>
        </div>
        <div class="mk-confirm-actions">
            <button type="button" class="btn btn-sm btn-outline-secondary" data-confirm-cancel autofocus>Batal</button>
            <button type="button" class="btn btn-sm mk-confirm-action" data-confirm-accept>Lanjutkan</button>
        </div>
    </div>
</dialog>
