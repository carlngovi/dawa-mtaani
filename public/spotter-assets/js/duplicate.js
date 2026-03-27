window.SpotterDuplicate = {
  normalise: function(name) {
    name = name.toLowerCase().trim();
    name = name.replace(/[^a-z0-9\s]/g, '');
    ['pharmacies','pharmacy','chemists','chemist','pharmas','pharma','pharms','pharm'].forEach(function(s) {
      name = name.replace(new RegExp('\\b' + s + '\\b', 'g'), 'pharm');
    });
    return name.replace(/\s+/g, ' ').trim();
  },
  levenshtein: function(a, b) {
    var m = a.length, n = b.length;
    var dp = [];
    for (var i = 0; i <= m; i++) {
      dp[i] = [];
      for (var j = 0; j <= n; j++) {
        if (i === 0) dp[i][j] = j;
        else if (j === 0) dp[i][j] = i;
        else dp[i][j] = 0;
      }
    }
    for (var i = 1; i <= m; i++)
      for (var j = 1; j <= n; j++)
        dp[i][j] = a[i-1] === b[j-1] ? dp[i-1][j-1] : 1 + Math.min(dp[i-1][j], dp[i][j-1], dp[i-1][j-1]);
    return dp[m][n];
  },
  check: async function(pharmacyName, ward) {
    var norm = this.normalise(pharmacyName);
    var registry = await window.SpotterDB.getAllRegistry();
    for (var k = 0; k < registry.length; k++) {
      var entry = registry[k];
      var entryNorm = this.normalise(entry.pharmacy || '');
      if (entryNorm === norm) return { type: 'exact', match: entry.pharmacy };
      if (this.levenshtein(norm, entryNorm) <= 2 && entry.ward === ward) {
        return { type: 'fuzzy', match: entry.pharmacy };
      }
    }
    return null;
  }
};
