        </div> <!-- .admin-content -->
    </main>

    <!-- Fenêtre Modale de Confirmation de Suppression Universelle (Pop-up) -->
    <div id="deleteConfirmModal" class="modal-overlay" onclick="handleDeleteModalOutsideClick(event)">
        <div class="modal-container" style="max-width:500px;">
            <div class="modal-header" style="background-color: var(--admin-danger);">
                <h3 style="color:#FFF; margin:0; font-size:1.15rem;">🗑️ Confirmation de Suppression</h3>
                <button type="button" class="modal-close" onclick="closeDeleteConfirmModal()">&times;</button>
            </div>
            <div class="modal-body" style="text-align:center; padding:30px 25px;">
                <div style="font-size:3rem; margin-bottom:15px; line-height:1;">⚠️</div>
                <h4 id="deleteModalItemTitle" style="color:var(--admin-sidebar-bg); margin:0 0 10px; font-size:1.1rem; font-weight:bold;">Êtes-vous absolument sûr ?</h4>
                <p id="deleteModalMessage" style="color:var(--admin-text-muted); font-size:0.95rem; margin:0; line-height:1.4;">Cette action est définitive et irréversible.</p>
            </div>
            <div class="modal-footer" style="justify-content:center; gap:15px; padding:18px 25px; background-color:#FAFAFA;">
                <button type="button" class="btn-action btn-secondary" onclick="closeDeleteConfirmModal()" style="padding:10px 22px; cursor:pointer;">Annuler</button>
                <a id="deleteModalConfirmBtn" href="#" class="btn-action btn-danger" style="padding:10px 25px; text-decoration:none; cursor:pointer;">Oui, Supprimer</a>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(url, title, typeName) {
            const modal = document.getElementById('deleteConfirmModal');
            const confirmBtn = document.getElementById('deleteModalConfirmBtn');
            const titleEl = document.getElementById('deleteModalItemTitle');
            const msgEl = document.getElementById('deleteModalMessage');

            if (modal && confirmBtn) {
                confirmBtn.href = url;
                if (titleEl) titleEl.textContent = title ? `Voulez-vous supprimer "${title}" ?` : 'Confirmer la suppression ?';
                if (msgEl) msgEl.textContent = typeName ? `Cette action supprimera définitivement cet élément (${typeName}) de la base de données.` : 'Cette action est définitive et irréversible.';
                
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
            return false;
        }

        function closeDeleteConfirmModal() {
            const modal = document.getElementById('deleteConfirmModal');
            if (modal) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function handleDeleteModalOutsideClick(event) {
            if (event.target.id === 'deleteConfirmModal') {
                closeDeleteConfirmModal();
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDeleteConfirmModal();
            }
        });
    </script>

</body>
</html>
