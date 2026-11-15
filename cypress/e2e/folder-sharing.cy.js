describe('Folder sharing', () => {
    const folder = 'Folder Share Test';
    const receiver = 'max';
    const api = '/index.php/apps/passwords/api/1.0';
    const auth = {user: 'admin', pass: 'admin'};

    let folderId = null;

    before(() => {
        cy.occ('user:setting', 'admin', 'core', 'lang', 'en');
        cy.occ('user:setting', 'admin', 'core', 'locale', 'en_US');

        cy.request({
            method: 'POST',
            url   : api + '/folder/create',
            auth,
            body  : {label: folder, parent: '00000000-0000-0000-0000-000000000000'},
            failOnStatusCode: false
        }).then((response) => {
            folderId = response.body.id || null;
        });
    });

    after(() => {
        if(folderId === null) return;

        cy.request({
            method: 'DELETE',
            url   : api + '/folder/delete',
            auth,
            body  : {id: folderId},
            failOnStatusCode: false
        });
    });

    beforeEach(() => {
        cy.login();
        cy.visit('/apps/passwords/#/folders', {retryOnNetworkFailure: true});
        cy.get('[data-pw-role="content"] [data-pw-label="' + folder + '"]', {timeout: 15000});
    });

    it('shares a folder with another user', () => {
        cy.itemAction({type: 'folder', label: folder}, 'share');

        cy.get('#tab-share-tab .share-edit-form input.vs__search', {timeout: 10000}).type(receiver);
        cy.get('#' + receiver + '.option').click();
        cy.get('#tab-share-tab .share-edit-actions .button-vue--vue-primary').click();

        cy.get('#tab-share-tab .share-list .list-item-content__name', {timeout: 30000}).should('contain', receiver);

        // revoke again so the spec can run repeatedly
        cy.get('#tab-share-tab .share-list [data-share-id] .list-item-content__actions button.action-item__menutoggle').click({force: true});
        cy.get('.v-popper__inner li').contains('Revoke access').click({force: true});
        cy.get('#tab-share-tab .empty-content', {timeout: 30000});
    });

    it('opens the folder details from the action menu', () => {
        cy.itemAction({type: 'folder', label: folder}, 'details');

        cy.get('.passwords-folder-info', {timeout: 10000}).should('contain', folder);
        cy.get('#tab-button-share-tab').should('exist');
    });

    it('offers the sharing tab with a user select', () => {
        cy.itemAction({type: 'folder', label: folder}, 'share');

        cy.get('#tab-share-tab .share-edit-form input.vs__search', {timeout: 10000}).should('be.visible');
    });
});
