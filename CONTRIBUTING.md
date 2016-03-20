## Reporting bugs

If you happen to find a bug, we kindly request you to report it. However, before submitting it, please report it using Github by following these 3 points:

  * Check if the bug is not already reported!
  * A clear title to resume the issue
  * A description of the workflow needed to reproduce the bug,

> _NOTE:_ Don’t hesitate giving as much information as you can (OS, PHP version extensions …)

## Pull requests

### Matching coding standards

Before each commit, be sure to match symfony coding standards by running the following command for fix:

```bash
make cs
```

And then, add fixed file to your commit before push.

### Sending a Pull Request

When you send a PR, just make sure that:

* You add valid test cases.
* Tests are green.
* The related documentation is up-to-date.
* You make the PR on the same branch you based your changes on. If you see commits
that you did not make in your PR, you're doing it wrong.
* Also don't forget to add a comment when you update a PR with a ping to the maintainer (``@username``), so he/she will get a notification.
