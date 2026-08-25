<?php 
$activeTab = 'users';
require_once APP_PATH . '/Views/layouts/admin_header.php'; 
?>

<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px; flex-wrap:wrap; gap:15px;">
    <div>
        <h1 style="color:var(--admin-sidebar-bg); margin:0;">Gestion des Agents Municipaux (RBAC)</h1>
        <p style="color:var(--admin-text-muted); margin:5px 0 0; font-size:0.95rem;">Administration des accès, rôles d'habilitation et statuts de comptes de la mairie</p>
    </div>
    <button type="button" onclick="openAddAgentModal()" class="btn-action btn-success" style="padding:12px 24px; font-size:0.95rem; cursor:pointer; border:none; display:flex; align-items:center; gap:8px;">
        <span style="font-size:1.1rem; font-weight:bold;">+</span> Ajouter un Agent Municipal
    </button>
</div>

<?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success"><?= Security::sanitize($flashSuccess) ?></div>
<?php endif; ?>

<?php if (!empty($flashError)): ?>
    <div class="alert alert-danger"><?= Security::sanitize($flashError) ?></div>
<?php endif; ?>

<!-- Tableau des Agents -->
<div class="table-card">
    <div class="table-header">
        <h3 style="margin:0; color:var(--admin-sidebar-bg);">Comptes d'Accès de la Mairie</h3>
    </div>
    <table class="admin-table">
        <thead>
            <tr>
                <th>Agent Municipal</th>
                <th>Identifiant</th>
                <th>Adresse E-mail</th>
                <th>Rôle & Habilitation</th>
                <th>État du Compte</th>
                <th>Date de Création</th>
                <th style="text-align:center;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <strong style="color:var(--admin-sidebar-bg);"><?= Security::sanitize($user['full_name']) ?></strong>
                        </td>
                        <td><code><?= Security::sanitize($user['username']) ?></code></td>
                        <td><?= Security::sanitize($user['email']) ?></td>
                        <td>
                            <?php if ($user['role_id'] == 1): ?>
                                <span class="badge-status" style="background-color:#E8EAF6; color:#1A237E;">🔑 Super-Admin</span>
                            <?php else: ?>
                                <span class="badge-status badge-approved">📝 Agent Rédacteur</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (isset($user['is_active']) && (int)$user['is_active'] === 1): ?>
                                <span class="badge-status badge-published" style="display:inline-flex; align-items:center; gap:4px;">
                                    🟢 Actif
                                </span>
                            <?php else: ?>
                                <span class="badge-status badge-rejected" style="display:inline-flex; align-items:center; gap:4px;">
                                    🔴 Inactif / Suspendu
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                        <td style="text-align:center; white-space:nowrap;">
                            <!-- Modifier le Compte Agent (Pop-up Modal) -->
                            <button type="button" onclick='openEditAgentModal(<?= json_encode($user, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn-action btn-secondary" title="Modifier le compte de cet agent" style="padding:8px 12px; margin-right:4px; border:none; cursor:pointer;">
                                ✏️
                            </button>

                            <!-- Supprimer le Compte -->
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="<?= BASE_URL ?>/admin/users/delete/<?= $user['id'] ?>" class="btn-action btn-danger" title="Supprimer le compte de cet agent" onclick="return confirmDelete('<?= BASE_URL ?>/admin/users/delete/<?= $user['id'] ?>', '<?= Security::sanitize($user['full_name']) ?>', 'Agent Municipal');" style="padding:8px 12px;">
                                    🗑️
                                </a>
                            <?php else: ?>
                                <span style="color:var(--admin-text-muted); font-size:0.8rem; font-style:italic;">Compte connecté</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align:center; padding:30px; color:var(--admin-text-muted);">
                        Aucun agent enregistré pour le moment.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Fenêtre Modale d'Ajout d'Agent -->
<div id="agentModal" class="modal-overlay" onclick="handleModalOutsideClick(event)">
    <div class="modal-container">
        
        <div class="modal-header">
            <h3>+ Ajouter un Nouvel Agent Municipal</h3>
            <button type="button" class="modal-close" onclick="closeAddAgentModal()">&times;</button>
        </div>

        <form action="<?= BASE_URL ?>/admin/users/store" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="modal-body">
                <div class="form-group-grid">
                    <div class="form-field">
                        <label for="full_name">Nom & Prénom de l'agent *</label>
                        <input type="text" id="full_name" name="full_name" required placeholder="ex: Mamadou Ndiaye">
                    </div>
                    <div class="form-field">
                        <label for="username">Identifiant de connexion *</label>
                        <input type="text" id="username" name="username" required placeholder="ex: mndiaye">
                    </div>
                </div>

                <div class="form-group-grid">
                    <div class="form-field">
                        <label for="email">Adresse E-mail institutionnelle *</label>
                        <input type="email" id="email" name="email" required placeholder="ex: mndiaye@tattaguine.gouv.sn">
                    </div>
                    <div class="form-field">
                        <label for="password">Mot de passe initial *</label>
                        <input type="password" id="password" name="password" required placeholder="••••••••">
                    </div>
                </div>

                <div class="form-group-grid">
                    <div class="form-field">
                        <label for="role_id">Rôle et Niveau d'Accès *</label>
                        <select id="role_id" name="role_id" required>
                            <option value="2">Agent Rédacteur & Modérateur</option>
                            <option value="1">Super-Administrateur (Gestion complète)</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="is_active">État du Compte *</label>
                        <select id="is_active" name="is_active" required>
                            <option value="1">🟢 Actif (Accès autorisé)</option>
                            <option value="0">🔴 Inactif (Accès suspendu)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action btn-secondary" onclick="closeAddAgentModal()" style="padding:10px 20px;">Annuler</button>
                <button type="submit" class="btn-action btn-success" style="padding:10px 25px; border:none; cursor:pointer;">Créer le Compte Agent</button>
            </div>
        </form>

    </div>
</div>

<!-- Fenêtre Modale de MODIFICATION d'Agent Municipal -->
<div id="editAgentModal" class="modal-overlay" onclick="handleEditAgentModalOutsideClick(event)">
    <div class="modal-container">
        
        <div class="modal-header" style="background-color:#1D437A;">
            <h3>✏️ Modifier le Compte Agent</h3>
            <button type="button" class="modal-close" onclick="closeEditAgentModal()">&times;</button>
        </div>

        <form id="editAgentForm" action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

            <div class="modal-body">
                <div class="form-group-grid">
                    <div class="form-field">
                        <label for="edit_full_name">Nom & Prénom de l'agent *</label>
                        <input type="text" id="edit_full_name" name="full_name" required>
                    </div>
                    <div class="form-field">
                        <label for="edit_username">Identifiant de connexion *</label>
                        <input type="text" id="edit_username" name="username" required>
                    </div>
                </div>

                <div class="form-group-grid">
                    <div class="form-field">
                        <label for="edit_email">Adresse E-mail institutionnelle *</label>
                        <input type="email" id="edit_email" name="email" required>
                    </div>
                    <div class="form-field">
                        <label for="edit_password">Changer le mot de passe (Optionnel)</label>
                        <input type="password" id="edit_password" name="password" placeholder="Laisser vide pour ne pas changer">
                    </div>
                </div>

                <div class="form-group-grid">
                    <div class="form-field">
                        <label for="edit_role_id">Rôle et Niveau d'Accès *</label>
                        <select id="edit_role_id" name="role_id" required>
                            <option value="2">Agent Rédacteur & Modérateur</option>
                            <option value="1">Super-Administrateur (Gestion complète)</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="edit_is_active">État du Compte (Géré par Super-Admin) *</label>
                        <select id="edit_is_active" name="is_active" required>
                            <option value="1">🟢 Actif (Accès autorisé)</option>
                            <option value="0">🔴 Inactif / Suspendu (Accès bloqué)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-action btn-secondary" onclick="closeEditAgentModal()" style="padding:10px 20px;">Annuler</button>
                <button type="submit" class="btn-action btn-success" style="padding:10px 25px; border:none; cursor:pointer;">Enregistrer les Modifications</button>
            </div>
        </form>

    </div>
</div>

<script>
    // --- Modale Ajout ---
    function openAddAgentModal() {
        const modal = document.getElementById('agentModal');
        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            const firstInput = modal.querySelector('input[name="full_name"]');
            if (firstInput) firstInput.focus();
        }
    }

    function closeAddAgentModal() {
        const modal = document.getElementById('agentModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function handleModalOutsideClick(event) {
        if (event.target.id === 'agentModal') {
            closeAddAgentModal();
        }
    }

    // --- Modale Modification ---
    function openEditAgentModal(user) {
        const modal = document.getElementById('editAgentModal');
        const form = document.getElementById('editAgentForm');
        
        if (modal && form) {
            form.action = '<?= BASE_URL ?>/admin/users/update/' + user.id;
            document.getElementById('edit_full_name').value = user.full_name || '';
            document.getElementById('edit_username').value = user.username || '';
            document.getElementById('edit_email').value = user.email || '';
            document.getElementById('edit_role_id').value = user.role_id || 2;
            document.getElementById('edit_is_active').value = (user.is_active !== undefined) ? user.is_active : 1;
            document.getElementById('edit_password').value = '';
            
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeEditAgentModal() {
        const modal = document.getElementById('editAgentModal');
        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    function handleEditAgentModalOutsideClick(event) {
        if (event.target.id === 'editAgentModal') {
            closeEditAgentModal();
        }
    }

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAddAgentModal();
            closeEditAgentModal();
        }
    });

    <?php if (!empty($flashError)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            // Re-ouvrir en cas d'erreur de validation
        });
    <?php endif; ?>
</script>

<?php require_once APP_PATH . '/Views/layouts/admin_footer.php'; ?>
