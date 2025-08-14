#!/usr/bin/env python3
"""
Development Workflow Metrics Collection Script

This script collects metrics about the effectiveness of the Development Workflow
from GitHub and stores them for analysis and improvement.
"""

import os
import json
import requests
from datetime import datetime, timedelta
from typing import Dict, List, Any

class WorkflowMetricsCollector:
    def __init__(self, token: str, repo: str):
        self.token = token
        self.repo = repo
        self.headers = {
            'Authorization': f'token {token}',
            'Accept': 'application/vnd.github.v3+json'
        }
        self.base_url = 'https://api.github.com'
        
    def collect_metrics(self) -> Dict[str, Any]:
        """Collect all workflow metrics"""
        print("🔍 Collecting Development Workflow metrics...")
        
        metrics = {
            'timestamp': datetime.utcnow().isoformat(),
            'repository': self.repo,
            'period': 'weekly',
            'issues': self._collect_issue_metrics(),
            'pull_requests': self._collect_pr_metrics(),
            'workflow_triggers': self._collect_workflow_metrics(),
            'labels': self._collect_label_metrics()
        }
        
        return metrics
    
    def _collect_issue_metrics(self) -> Dict[str, Any]:
        """Collect metrics about issues and their consistency check status"""
        print("  📋 Collecting issue metrics...")
        
        # Get all issues from the last week
        since_date = (datetime.utcnow() - timedelta(days=7)).strftime('%Y-%m-%d')
        
        issues_url = f"{self.base_url}/repos/{self.repo}/issues"
        params = {
            'state': 'all',
            'since': since_date,
            'per_page': 100
        }
        
        response = requests.get(issues_url, headers=self.headers, params=params)
        issues = response.json()
        
        total_issues = len(issues)
        needs_consistency_check = 0
        consistency_completed = 0
        
        for issue in issues:
            labels = [label['name'] for label in issue.get('labels', [])]
            
            if 'needs-consistency-check' in labels:
                needs_consistency_check += 1
            elif 'consistency-check-completed' in labels:
                consistency_completed += 1
        
        return {
            'total_issues': total_issues,
            'needs_consistency_check': needs_consistency_check,
            'consistency_completed': consistency_completed,
            'completion_rate': (consistency_completed / total_issues * 100) if total_issues > 0 else 0
        }
    
    def _collect_pr_metrics(self) -> Dict[str, Any]:
        """Collect metrics about pull requests and their consistency check status"""
        print("  🔄 Collecting PR metrics...")
        
        # Get all PRs from the last week
        since_date = (datetime.utcnow() - timedelta(days=7)).strftime('%Y-%m-%d')
        
        prs_url = f"{self.base_url}/repos/{self.repo}/pulls"
        params = {
            'state': 'all',
            'since': since_date,
            'per_page': 100
        }
        
        response = requests.get(prs_url, headers=self.headers, params=params)
        prs = response.json()
        
        total_prs = len(prs)
        needs_consistency_check = 0
        consistency_completed = 0
        red_flags_detected = 0
        
        for pr in prs:
            labels = [label['name'] for label in pr.get('labels', [])]
            
            if 'needs-consistency-check' in labels:
                needs_consistency_check += 1
            elif 'consistency-check-completed' in labels:
                consistency_completed += 1
                
            # Check for red flags in PR body
            if pr.get('body'):
                body_lower = pr['body'].lower()
                red_flag_indicators = [
                    "doesn't need user roles",
                    "doesn't need to test",
                    "different framework",
                    "skip authorization",
                    "no need for permissions"
                ]
                
                if any(indicator in body_lower for indicator in red_flag_indicators):
                    red_flags_detected += 1
        
        return {
            'total_prs': total_prs,
            'needs_consistency_check': needs_consistency_check,
            'consistency_completed': consistency_completed,
            'red_flags_detected': red_flags_detected,
            'completion_rate': (consistency_completed / total_prs * 100) if total_prs > 0 else 0
        }
    
    def _collect_workflow_metrics(self) -> Dict[str, Any]:
        """Collect metrics about workflow triggers and effectiveness"""
        print("  ⚡ Collecting workflow metrics...")
        
        # Get workflow runs from the last week
        since_date = (datetime.utcnow() - timedelta(days=7)).strftime('%Y-%m-%d')
        
        workflows_url = f"{self.base_url}/repos/{self.repo}/actions/runs"
        params = {
            'since': since_date,
            'per_page': 100
        }
        
        response = requests.get(workflows_url, headers=self.headers, params=params)
        workflow_runs = response.json().get('workflow_runs', [])
        
        consistency_check_runs = 0
        successful_runs = 0
        failed_runs = 0
        
        for run in workflow_runs:
            if 'consistency-check' in run.get('name', '').lower():
                consistency_check_runs += 1
                
                if run.get('conclusion') == 'success':
                    successful_runs += 1
                elif run.get('conclusion') == 'failure':
                    failed_runs += 1
        
        return {
            'total_consistency_check_runs': consistency_check_runs,
            'successful_runs': successful_runs,
            'failed_runs': failed_runs,
            'success_rate': (successful_runs / consistency_check_runs * 100) if consistency_check_runs > 0 else 0
        }
    
    def _collect_label_metrics(self) -> Dict[str, Any]:
        """Collect metrics about label usage and effectiveness"""
        print("  🏷️  Collecting label metrics...")
        
        labels_url = f"{self.base_url}/repos/{self.repo}/labels"
        response = requests.get(labels_url, headers=self.headers)
        labels = response.json()
        
        workflow_labels = {}
        for label in labels:
            if any(keyword in label['name'].lower() for keyword in ['consistency', 'workflow', 'needs']):
                workflow_labels[label['name']] = {
                    'name': label['name'],
                    'color': label['color'],
                    'description': label.get('description', ''),
                    'count': 0  # We'll need to count usage separately
                }
        
        return {
            'workflow_labels': list(workflow_labels.keys()),
            'total_workflow_labels': len(workflow_labels)
        }

def main():
    """Main function to collect and store metrics"""
    # Get GitHub token from environment
    token = os.getenv('GITHUB_TOKEN')
    if not token:
        print("❌ GITHUB_TOKEN environment variable not set")
        return
    
    # Get repository from environment or use default
    repo = os.getenv('GITHUB_REPOSITORY', 'your-username/thorium90')
    
    # Initialize collector
    collector = WorkflowMetricsCollector(token, repo)
    
    # Collect metrics
    metrics = collector.collect_metrics()
    
    # Print summary
    print("\n📊 Metrics Summary:")
    print(f"  Issues: {metrics['issues']['total_issues']} total, {metrics['issues']['completion_rate']:.1f}% completion rate")
    print(f"  PRs: {metrics['pull_requests']['total_prs']} total, {metrics['pull_requests']['completion_rate']:.1f}% completion rate")
    print(f"  Red Flags: {metrics['pull_requests']['red_flags_detected']} detected")
    print(f"  Workflows: {metrics['workflow_triggers']['total_consistency_check_runs']} runs, {metrics['workflow_triggers']['success_rate']:.1f}% success rate")
    
    # Store metrics
    output_file = 'workflow-metrics.json'
    with open(output_file, 'w') as f:
        json.dump(metrics, f, indent=2)
    
    print(f"\n✅ Metrics saved to {output_file}")
    
    # Set output for GitHub Actions
    if os.getenv('GITHUB_ACTIONS'):
        print(f"::set-output name=metrics::{json.dumps(metrics)}")

if __name__ == '__main__':
    main()
