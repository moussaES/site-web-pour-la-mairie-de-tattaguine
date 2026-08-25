<?php 
$activeTab = 'documents';
require_once APP_PATH . '/Views/layouts/admin_header.php'; 
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; flex-wrap:wrap; gap:15px;">
    <div>
        <h1 style="color:var(--admin-sidebar-bg); margin:0;">Gestion des Documents Administratifs</h1>
        <p style="color:var(--admin-text-muted); margin:5px 0 0; font-size:0.95rem;">Bibliothèque des formulaires, arrêtés et délibérations officiels de la Mairie</p>
    </div>
    <button type="button" onclick="openAddDocumentModal()" class="btn-action btn-success" style="padding:12px 24px; font-size:0.95rem; cursor:pointer; border:none; display:flex; align-items:center; gap:8px;">
        <span style="font-size:1.1rem; font-weight:bold;">+</span> Téléverser un Document PDF
    </button>
</div>

<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success"><?= Security::sanitize($flashSuccess) ?></div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-danger"><?= Security::sanitize($flashError) ?></div>
<?php endif; ?>

<!-- Tableau des Documents -->
<div class="table-card">
    <div class="table-header">
        <h3 style="margin:0; color:var(--admin-sidebar-bg);">Documents Officiels Téléchargeables</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Titre du Document</th>
                <th>Catégorie Administrative</th>
                <th>Taille</th>
                <th>Téléchargements</th>
                <th>Mis en ligne par</th>
                <th>Date</th>
                <th style="text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($documents)): ?>
                <?php foreach ($documents as $doc): ?>
                    <tr>
                        <td>
                            <strong style="color:var(--admin-sidebar-bg);"><?= Security::sanitize($doc['title']) ?></strong>
                            <?php if (!empty($doc['description'])): ?>
                                <small style="display:block; color:var(--admin-text-muted); font-weight:normal;"><?= Security::sanitize($doc['description']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge-status" style="background-color:#E8F5E9; color:#2E7D32;"><?= Security::sanitize($doc['category']) ?></span>
                        </td>
                        <td><?= Security::sanitize($doc['file_size']) ?></td>
                        <td><strong><?= $doc['downloads_count'] ?></strong> fois</td>
                        <td><?= Security::sanitize($doc['author_name']) ?></td>
                        <td><?= date('d/m/Y', strtotime($doc['created_at'])) ?></td>
                        <td style="text-align:center; white-space:nowrap;">
                            <!-- Icône Visualiser / Ouvrir -->
                            <a href="<?= BASE_URL ?>/<?= Security::sanitize($doc['file_path']) ?>" target="_blank" class="btn-action btn-info" title="Ouvrir le PDF" style="padding:8px 12px; margin-right:4px;">
                                👁️
                            </a>
                            
                            <!-- Icône Éditer / Modifier (Pop-up Modal) -->
                            <button type="button" onclick='openEditDocumentModal(<?= json_encode($doc, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn-action btn-secondary" title="Modifier ce document" style="padding:8px 12px; margin-right:4px; border:none; cursor:pointer;">
                                ✏️
                            </button>

                            <!-- Icône Supprimer -->
                            <a href="<?= BASE_URL ?>/admin/documents/delete/<?= $doc['id'] ?>" class="btn-action btn-danger" title="Supprimer ce document" onclick="return confirmDelete('<?= BASE_URL ?>/admin/documents/delete/<?= $doc['id'] ?>', '<?= Security::sanitize($doc['title']) ?>', 'Document PDF');" style="padding:8px 12px;">
                                🗑️
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:30px; color:var(--admin-text-muted);">
                        Aucun document disponible dans la bibliothèque. Cliquez sur <strong>+ Téléverser un Document PDF</strong> pour en ajouter un.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Fenêtre Modale de TÉLÉVERSEMENT de Document -->
<div id="documentModal" class="modal-overlay" onclick="handleDocModalOutsideClick(event)">
    <div class="modal-container">
        
        <div class="modal-header">
            <h3>Téléverser un Nouveau Document PDF</h3>
            <button type="button" class="modal-close" onclick="closeAddDocumentModal()">&times;</button>
        </div>

        <form action="<?= BASE_URL ?>/admin/documents/store" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="modal-body">
                <div class="form-group-grid">
                    <div class="form-field">
                        <label for="doc_title">Titre du document *</label>
                        <input type="text" id="doc_title" name="title" required placeholder="ex: Formulaire de demande d'acte de naissance">
                    </div>
                    <div class="form-field">
                        <label for="doc_category">Catégorie administrative *</label>
                        <select id="doc_category" name="category" required>
                            <option value="État Civil">État Civil</option>
                            <option value="Arrêtés Municipaux">Arrêtés Municipaux</option>
                            <option value="Délibérations">Délibérations du Conseil</option>
                            <option value="Budgets & Comptes">Budgets & Comptes</option>
                            <option value="Formulaires">Formulaires & Démarches</option>
                        </select>
                    </div>
                </div>

                <div class="form-field" style="margin-bottom:18px;">
                    <label for="doc_description">Description courte (Facultatif)</label>
                    <textarea id="doc_description" name="description" rows="3" placeholder="Précisions sur les pièces à fournir ou la procédure..." style="padding:10px; border:1px solid #CCC; border-radius:6px; font-family:inherit;"></textarea>
                </div>

                <div class="form-field">
                    <label for="doc_file">Fichier PDF Officiel *</label>
                    <input type="file" id="doc_file" name="file" accept=".pdf" required style="padding:8px; background:#F8F9FA;">
                    <small style="color:var(--admin-text-muted);">Format autorisé : .PDF uniquement (Taille max : 10 Mo)</small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action btn-secondary" onclick="closeAddDocumentModal()" style="padding:10px 20px;">Annuler</button>
                <button type="submit" class="btn-action btn-success" style="padding:10px 25px; border:none; cursor:pointer;">Mettre en Ligne</button>
            </div>
        </form>

    </div>
</div>

<!-- Fenêtre Modale de MODIFICATION (ÉDITION) de Document -->
<div id="editDocumentModal" class="modal-overlay" onclick="handleEditDocModalOutsideClick(event)">
    <div class="modal-container">
        
        <div class="modal-header" style="background-color:#1D437A;">
            <h3>✏️ Modifier le Document Administratif</h3>
            <button type="button" class="modal-close" onclick="closeEditDocumentModal()">&times;</button>
        </div>

        <form id="editDocumentForm" action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="modal-body">
                <div class="form-group-grid">
                    <div class="form-field">
                        <label for="edit_doc_title">Titre du document *</label>
                        <input type="text" id="edit_doc_title" name="title" required>
                    </div>
                    <div class="form-field">
                        <label for="edit_doc_category">Catégorie administrative *</label>
                        <select id="edit_doc_category" name="category" required>
                            <option value="État Civil">État Civil</option>
                            <option value="Arrêtés Municipaux">Arrêtés Municipaux</option>
                            <option value="Délibérations">Délibérations du Conseil</option>
                            <option value="Budgets & Comptes">Budgets & Comptes</option>
                            <option value="Formulaires">Formulaires & Démarches</option>
                        </select>
                    </div>
                </div>

                <div class="form-field" style="margin-bottom:18px;">
                    <label for="edit_doc_description">Description courte</label>
                    <textarea id="edit_doc_description" name="description" rows="3" style="padding:10px; border:1px solid #CCC; border-radius:6px; font-family:inherit;"></textarea>
                </div>

                <div class="form-field">
                    <label for="edit_doc_file">Remplacer le fichier PDF (Optionnel)</label>
                    <input type="file" id="edit_doc_file" name="file" accept=".pdf" style="padding:8px; background:#F8F9FA;">
                    <small style="color:var(--admin-text-muted);">Laissez ce champ vide pour conserver le fichier PDF actuel.</small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action btn-secondary" onclick="closeEditDocumentModal()" style="padding:10px 20px;">Annuler</button>
                <button type="submit" class="btn-action btn-success" style="padding:10px 25px; border:none; cursor:pointer;">Enregistrer les Modifications</button>
            </div>
        </form>

    </div>
</div>

<script>
    // --- Modale Ajout ---
    function openAddDocumentModal() {
        const modal = document.getElementById('documentModal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            const firstInput = modal.querySelector('input[name="title"]');
            if (firstInput) firstInput.focus();
        }
    }

    function closeAddDocumentModal() {
        const modal = document.getElementById('documentModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function handleDocModalOutsideClick(event) {
        if (event.target.id === 'documentModal') {
            closeAddDocumentModal();
        }
    }

    // --- Modale Modification (Édition) ---
    function openEditDocumentModal(doc) {
        const modal = document.getElementById('editDocumentModal');
        const form = document.getElementById('editDocumentForm');
        
        if (modal && form) {
            form.action = '<?= BASE_URL ?>/admin/documents/update/' + doc.id;
            document.getElementById('edit_doc_title').value = doc.title || '';
            document.getElementById('edit_doc_category').value = doc.category || 'État Civil';
            document.getElementById('edit_doc_description').value = doc.description || '';
            
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeEditDocumentModal() {
        const modal = document.getElementById('editDocumentModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function handleEditDocModalOutsideClick(event) {
        if (event.target.id === 'editDocumentModal') {
            closeEditDocumentModal();
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAddDocumentModal();
            closeEditDocumentModal();
        }
    });

    <?php if (!empty($flashError)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Re-ouvrir si erreur de validation
        });
    <?php endif; ?>
</script>

<?php require_once APP_PATH . '/Views/layouts/admin_footer.php'; ?>
