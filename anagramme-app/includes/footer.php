    </main>
    <?php if (is_logged_in()): ?>
        <form method="post" action="logout.php" class="logout">
            <?= csrf_field() ?>
            <button type="submit" class="logout-btn" title="Se déconnecter" aria-label="Se déconnecter">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
            </button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
