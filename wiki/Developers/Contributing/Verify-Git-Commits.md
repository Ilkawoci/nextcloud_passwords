## Create and add a ssh key
Follow [this GitLab tutorial](https://docs.gitlab.com/ee/user/project/repository/gpg_signed_commits/index.html) or [this GitHub tutorial](https://help.github.com/articles/signing-commits/).

## Project specific verify settings
You can set the git signing settings on a per-project base.
Use the following commands to sign the commits in a project with your gpg key.
```bash
git config user.signingkey YOURKEY
git config commit.gpgsign true
```

## Use with IDE (PHPStorm etc.)
To use git signing with your IDE, you will have to edit your GnuPG configuration.
```bash
nano ~/.gnupg/gpg.conf
```
Add these lines to the end of your configuration:
```bash
no-tty
use-agent
```